<?php

// Server-side script for testing database connectivity
//Runs only on the server; accepts program/courses payloads and validates against DB

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

$dbHost = 'imc.kean.edu';
$dbName = '2025F_CPS4301_01';
$dbUser = '2025F_CPS4301_01';
$dbPass = '2025F_CPS4301_01';

function getDBConnection() {
    global $dbHost, $dbName, $dbUser, $dbPass;
    
    try {
        $pdo = new PDO(
            "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['program_code']) || !isset($input['courses'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    $programCode = trim($input['program_code']);
    $studentCourses = $input['courses'];
    
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT program_id FROM program WHERE code = ?");
    $stmt->execute([$programCode]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Program not found']);
        exit;
    }
    
    $requirements = getProgramRequirements($pdo, $programCode);
    
    if (!$requirements || isset($requirements['error'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch requirements']);
        exit;
    }
    
    $completedCourses = [];
    foreach ($studentCourses as $course) {
        $key = strtoupper(trim($course['Course Code']));
        $completedCourses[$key] = [
            'name' => $course['Course Name'],
            'credits' => floatval($course['Credits']),
            'grade' => $course['Grade'],
            'semester' => $course['Semester']
        ];
    }
    
    $usedCourses = [];
    
    function matchCourse($courseCode, $completedCourses) {
        $code = strtoupper(trim($courseCode));
        return isset($completedCourses[$code]);
    }
    
    function getCourseKey($subject, $numberCode) {
        return strtoupper(trim($subject) . ' ' . trim($numberCode));
    }
    
    $result = [
        'program' => $requirements['program'],
        'total_completed_credits' => array_sum(array_column($studentCourses, 'Credits')),
        'categories' => []
    ];
    
    $category = [
        'name' => 'GE - Foundation',
        'type' => 'ge_foundation',
        'fixed_courses' => [],
        'choice_courses' => [],
        'completed_count' => 0,
        'total_count' => 0,
        'completed_credits' => 0,
        'required_credits' => 0
    ];
    
    foreach ($requirements['ge_foundation']['fixed'] ?? [] as $course) {
        $courseKey = getCourseKey($course['subject'], $course['number_code']);
        $isCompleted = matchCourse($courseKey, $completedCourses);
        
        $category['fixed_courses'][] = [
            'subject' => $course['subject'],
            'number_code' => $course['number_code'],
            'title' => $course['title'],
            'credits' => $course['credits'],
            'completed' => $isCompleted
        ];
        
        $category['total_count']++;
        $category['required_credits'] += floatval($course['credits']);
        
        if ($isCompleted) {
            $category['completed_count']++;
            $category['completed_credits'] += $completedCourses[$courseKey]['credits'];
            $usedCourses[$courseKey] = true;
        }
    }
    
    if (!empty($requirements['ge_foundation']['transition_choices'])) {
        $completedTransitions = [];
        $availableTransitions = [];

        foreach ($requirements['ge_foundation']['transition_choices'] as $course) {
            $courseKey = getCourseKey($course['subject'], $course['number_code']);
            $isCompleted = matchCourse($courseKey, $completedCourses);

            if ($isCompleted) {
                $completedTransitions[] = [
                    'subject' => $course['subject'],
                    'number_code' => $course['number_code'],
                    'title' => $course['title'],
                    'credits' => $course['credits'],
                    'completed' => true
                ];
                $category['completed_credits'] += $completedCourses[$courseKey]['credits'];
                $category['completed_count']++;
                $usedCourses[$courseKey] = true;
            } else {
                $availableTransitions[] = [
                    'subject' => $course['subject'],
                    'number_code' => $course['number_code'],
                    'title' => $course['title'],
                    'credits' => $course['credits'],
                    'completed' => false
                ];
            }
        }

        // Completed courses are displayed in fixed_courses
        foreach ($completedTransitions as $course) {
            $category['fixed_courses'][] = $course;
            $category['total_count']++;
        }

        // Available courses are displayed in choice_courses
        if (!empty($availableTransitions)) {
            $category['choice_courses'][] = [
                'label' => 'Foundation Transition (choose one)',
                'courses' => $availableTransitions
            ];
        }
    }
    
    $result['categories'][] = $category;
    
    $category = [
        'name' => 'GE - Humanities',
        'type' => 'ge_humanities',
        'fixed_courses' => [],
        'choice_courses' => [],
        'completed_count' => 0,
        'total_count' => 0,
        'completed_credits' => 0,
        'required_credits' => 0
    ];
    
    foreach ($requirements['ge_humanities']['fixed'] ?? [] as $course) {
        $courseKey = getCourseKey($course['subject'], $course['number_code']);
        $isCompleted = matchCourse($courseKey, $completedCourses);
        
        $category['fixed_courses'][] = [
            'subject' => $course['subject'],
            'number_code' => $course['number_code'],
            'title' => $course['title'],
            'credits' => $course['credits'],
            'completed' => $isCompleted
        ];
        
        $category['total_count']++;
        $category['required_credits'] += floatval($course['credits']);
        
        if ($isCompleted) {
            $category['completed_count']++;
            $category['completed_credits'] += $completedCourses[$courseKey]['credits'];
            $usedCourses[$courseKey] = true;
        }
    }
    
    if (!empty($requirements['ge_humanities']['choice_meta'])) {
        $meta = $requirements['ge_humanities']['choice_meta'];
        $category['choice_meta'] = [
            'label' => $meta['label'] ?? '',
            'select_min_count' => $meta['select_min_count'] ?? 0,
            'areas' => array_column($requirements['ge_humanities']['areas'] ?? [], 'area_label')
        ];
    }
    
    $result['categories'][] = $category;
    
    $category = [
        'name' => 'GE - Social Sciences',
        'type' => 'ge_social_sciences',
        'fixed_courses' => [],
        'choice_courses' => [],
        'completed_count' => 0,
        'total_count' => 0,
        'completed_credits' => 0,
        'required_credits' => 0
    ];
    
    foreach ($requirements['ge_social_sciences']['fixed'] as $course) {
        $courseKey = getCourseKey($course['subject'], $course['number_code']);
        $isCompleted = matchCourse($courseKey, $completedCourses);
        
        $category['fixed_courses'][] = [
            'subject' => $course['subject'],
            'number_code' => $course['number_code'],
            'title' => $course['title'],
            'credits' => $course['credits'],
            'completed' => $isCompleted
        ];
        
        $category['total_count']++;
        $category['required_credits'] += floatval($course['credits']);
        
        if ($isCompleted) {
            $category['completed_count']++;
            $category['completed_credits'] += $completedCourses[$courseKey]['credits'];
            $usedCourses[$courseKey] = true;
        }
    }
    
    if ($requirements['ge_social_sciences']['choice_meta']) {
        $meta = $requirements['ge_social_sciences']['choice_meta'];
        $category['choice_meta'] = [
            'label' => $meta['label'],
            'select_min_count' => $meta['select_min_count'],
            'areas' => array_column($requirements['ge_social_sciences']['areas'], 'area_label')
        ];
    }
    
    $result['categories'][] = $category;
    
    $category = [
        'name' => 'GE - Science & Mathematics',
        'type' => 'ge_science_math',
        'fixed_courses' => [],
        'choice_courses' => [],
        'completed_count' => 0,
        'total_count' => 0,
        'completed_credits' => 0,
        'required_credits' => $requirements['ge_science_math']['meta']['min_credits'] ?? 0
    ];
    
    foreach ($requirements['ge_science_math']['fixed'] as $course) {
        $courseKey = getCourseKey($course['subject'], $course['number_code']);
        $isCompleted = matchCourse($courseKey, $completedCourses);
        
        $category['fixed_courses'][] = [
            'subject' => $course['subject'],
            'number_code' => $course['number_code'],
            'title' => $course['title'],
            'credits' => $course['credits'],
            'completed' => $isCompleted
        ];
        
        $category['total_count']++;
        
        if ($isCompleted) {
            $category['completed_count']++;
            $category['completed_credits'] += $completedCourses[$courseKey]['credits'];
            $usedCourses[$courseKey] = true;
        }
    }
    
    if (!empty($requirements['ge_science_math']['lab_science_i'])) {
        $completedLabSci = [];
        $availableLabSci = [];

        foreach ($requirements['ge_science_math']['lab_science_i'] as $course) {
            $courseKey = getCourseKey($course['subject'], $course['number_code']);
            $isCompleted = matchCourse($courseKey, $completedCourses);

            if ($isCompleted) {
                $completedLabSci[] = [
                    'subject' => $course['subject'],
                    'number_code' => $course['number_code'],
                    'title' => $course['title'],
                    'credits' => $course['credits'],
                    'completed' => true
                ];
                $category['completed_credits'] += $completedCourses[$courseKey]['credits'];
                $category['completed_count']++;
                $usedCourses[$courseKey] = true;
            } else {
                $availableLabSci[] = [
                    'subject' => $course['subject'],
                    'number_code' => $course['number_code'],
                    'title' => $course['title'],
                    'credits' => $course['credits'],
                    'completed' => false
                ];
            }
        }

        // Completed courses are displayed in fixed_courses
        foreach ($completedLabSci as $course) {
            $category['fixed_courses'][] = $course;
            $category['total_count']++;
        }

        // Available courses are displayed in choice_courses
        if (!empty($availableLabSci)) {
            $category['choice_courses'][] = [
                'label' => 'Lab Science I (choose one)',
                'courses' => $availableLabSci
            ];
        }
    }


    $result['categories'][] = $category;

    // Handle high-level MATH courses not in database (>=2000 level) - add to CS Additional Required v1
    $mathCoursesNotInDb = [];
    foreach ($completedCourses as $courseCode => $courseInfo) {
        if (isset($usedCourses[$courseCode])) {
            continue;
        }

        $parts = explode(' ', $courseCode);
        if (count($parts) >= 2) {
            $subject = $parts[0];
            $numberCode = $parts[1];
            $courseLevel = intval($numberCode);

            if ($subject === 'MATH' && $courseLevel >= 2000) {
                // Check if this MATH course exists in database
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM course WHERE subject = ? AND number_code = ?");
                $stmt->execute([$subject, $numberCode]);
                $exists = $stmt->fetch()['count'] > 0;

                if (!$exists) {
                    $mathCoursesNotInDb[] = [
                        'subject' => $subject,
                        'number_code' => $numberCode,
                        'title' => $courseInfo['name'],
                        'credits' => $courseInfo['credits'],
                        'course_code' => $courseCode
                    ];
                }
            }
        }
    }

    foreach ($requirements['additional_required']['sets'] as $set) {
        $setId = $set['add_set_id'];
        $category = [
            'name' => 'Additional Required - ' . $set['name'],
            'type' => 'additional_required',
            'fixed_courses' => [],
            'choice_courses' => [],
            'completed_count' => 0,
            'total_count' => 0,
            'completed_credits' => 0,
            'required_credits' => $set['min_credits']
        ];
        
        if (isset($requirements['additional_required']['fixed_by_set'][$setId])) {
            foreach ($requirements['additional_required']['fixed_by_set'][$setId] as $course) {
                $courseKey = getCourseKey($course['subject'], $course['number_code']);
                $isCompleted = matchCourse($courseKey, $completedCourses);
                
                $category['fixed_courses'][] = [
                    'subject' => $course['subject'],
                    'number_code' => $course['number_code'],
                    'title' => $course['title'],
                    'credits' => $course['credits'],
                    'min_grade' => $course['min_grade'],
                    'completed' => $isCompleted
                ];
                
                $category['total_count']++;
                
                if ($isCompleted) {
                    $category['completed_count']++;
                    $category['completed_credits'] += $completedCourses[$courseKey]['credits'];
                    $usedCourses[$courseKey] = true;
                }
            }
        }
        
        if (isset($requirements['additional_required']['choice_meta_by_set'][$setId])) {
            foreach ($requirements['additional_required']['choice_meta_by_set'][$setId] as $choiceMeta) {
                $choiceId = $choiceMeta['add_choice_set_id'];
                $choiceGroup = [
                    'label' => $choiceMeta['label'],
                    'code' => $choiceMeta['code'],
                    'select_min_count' => $choiceMeta['select_min_count'],
                    'select_min_credits' => $choiceMeta['select_min_credits'],
                    'courses' => [],
                    'subjects' => []
                ];
                
            $completedChoiceCourses = [];
            $availableChoiceCourses = [];

            if (isset($requirements['additional_required']['choice_courses_by_id'][$choiceId])) {
                foreach ($requirements['additional_required']['choice_courses_by_id'][$choiceId] as $course) {
                    $courseKey = getCourseKey($course['subject'], $course['number_code']);
                    $isCompleted = matchCourse($courseKey, $completedCourses);

                    if ($isCompleted) {
                        $completedChoiceCourses[] = [
                            'subject' => $course['subject'],
                            'number_code' => $course['number_code'],
                            'title' => $course['title'],
                            'credits' => $course['credits'],
                            'completed' => true
                        ];
                        $category['completed_credits'] += $completedCourses[$courseKey]['credits'];
                        $usedCourses[$courseKey] = true;
                    } else {
                        $availableChoiceCourses[] = [
                            'subject' => $course['subject'],
                            'number_code' => $course['number_code'],
                            'title' => $course['title'],
                            'credits' => $course['credits'],
                            'completed' => false
                        ];
                    }
                }
            }

            // Completed courses are displayed in fixed_courses
            foreach ($completedChoiceCourses as $course) {
                $category['fixed_courses'][] = $course;
                $category['total_count']++;
                $category['completed_count']++;
            }

            // Available courses are displayed in choice_courses
            if (!empty($availableChoiceCourses)) {
                $choiceGroup['courses'] = $availableChoiceCourses;
                $category['choice_courses'][] = $choiceGroup;
            } elseif (!empty($choiceGroup['subjects'])) {
                // If only subjects exist without specific courses, still show choice_courses
                $category['choice_courses'][] = $choiceGroup;
            }
                
                if (isset($requirements['additional_required']['choice_subjects_by_id'][$choiceId])) {
                    $choiceGroup['subjects'] = array_column(
                        $requirements['additional_required']['choice_subjects_by_id'][$choiceId],
                        'subject'
                    );
                }
                
                $category['choice_courses'][] = $choiceGroup;
            }
        }

        // Add MATH courses not in database (>=2000 level) to CS Additional Required v1
        if ($set['name'] === 'CS Additional Required v1' && !empty($mathCoursesNotInDb)) {
            foreach ($mathCoursesNotInDb as $course) {
                $category['fixed_courses'][] = [
                    'subject' => $course['subject'],
                    'number_code' => $course['number_code'],
                    'title' => $course['title'],
                    'credits' => $course['credits'],
                    'completed' => true
                ];

                $category['completed_count']++;
                $category['completed_credits'] += $course['credits'];
                $category['total_count']++;
                $usedCourses[$course['course_code']] = true;
            }
        }

        $result['categories'][] = $category;
    }

    foreach ($requirements['major_core']['sets'] as $set) {
        $setId = $set['core_set_id'];
        $category = [
            'name' => 'Major Core - ' . $set['name'],
            'type' => 'major_core',
            'fixed_courses' => [],
            'choice_courses' => [],
            'completed_count' => 0,
            'total_count' => 0,
            'completed_credits' => 0,
            'required_credits' => $set['min_credits']
        ];
        
        if (isset($requirements['major_core']['courses_by_set'][$setId])) {
            foreach ($requirements['major_core']['courses_by_set'][$setId] as $course) {
                $courseKey = getCourseKey($course['subject'], $course['number_code']);
                $isCompleted = matchCourse($courseKey, $completedCourses);
                
                $category['fixed_courses'][] = [
                    'subject' => $course['subject'],
                    'number_code' => $course['number_code'],
                    'title' => $course['title'],
                    'credits' => $course['credits'],
                    'min_grade' => $course['min_grade'],
                    'completed' => $isCompleted
                ];
                
                $category['total_count']++;
                
                if ($isCompleted) {
                    $category['completed_count']++;
                    $category['completed_credits'] += $completedCourses[$courseKey]['credits'];
                    $usedCourses[$courseKey] = true;
                }
            }
        }
        
        $result['categories'][] = $category;
    }
    
    foreach ($requirements['major_concentration']['sets'] as $set) {
        $setId = $set['conc_set_id'];
        $category = [
            'name' => 'Major Concentration - ' . $set['name'],
            'type' => 'major_concentration',
            'fixed_courses' => [],
            'choice_courses' => [],
            'completed_count' => 0,
            'total_count' => 0,
            'completed_credits' => 0,
            'required_credits' => $set['min_credits']
        ];
        
        if (isset($requirements['major_concentration']['fixed_by_set'][$setId])) {
            foreach ($requirements['major_concentration']['fixed_by_set'][$setId] as $course) {
                $courseKey = getCourseKey($course['subject'], $course['number_code']);
                $isCompleted = matchCourse($courseKey, $completedCourses);
                
                $category['fixed_courses'][] = [
                    'subject' => $course['subject'],
                    'number_code' => $course['number_code'],
                    'title' => $course['title'],
                    'credits' => $course['credits'],
                    'min_grade' => $course['min_grade'],
                    'completed' => $isCompleted
                ];
                
                $category['total_count']++;
                
                if ($isCompleted) {
                    $category['completed_count']++;
                    $category['completed_credits'] += $completedCourses[$courseKey]['credits'];
                    $usedCourses[$courseKey] = true;
                }
            }
        }
        
        if (isset($requirements['major_concentration']['choice_meta_by_set'][$setId])) {
            foreach ($requirements['major_concentration']['choice_meta_by_set'][$setId] as $choiceMeta) {
                $choiceId = $choiceMeta['conc_choice_set_id'];
                $choiceGroup = [
                    'label' => $choiceMeta['label'],
                    'code' => $choiceMeta['code'],
                    'select_min_count' => $choiceMeta['select_min_count'],
                    'select_min_credits' => $choiceMeta['select_min_credits'],
                    'courses' => []
                ];
                
            $completedChoiceCourses = [];
            $availableChoiceCourses = [];

            if (isset($requirements['major_concentration']['choice_courses_by_id'][$choiceId])) {
                foreach ($requirements['major_concentration']['choice_courses_by_id'][$choiceId] as $course) {
                    $courseKey = getCourseKey($course['subject'], $course['number_code']);
                    $isCompleted = matchCourse($courseKey, $completedCourses);

                    if ($isCompleted) {
                        $completedChoiceCourses[] = [
                            'subject' => $course['subject'],
                            'number_code' => $course['number_code'],
                            'title' => $course['title'],
                            'credits' => $course['credits'],
                            'completed' => true
                        ];
                        $category['completed_credits'] += $completedCourses[$courseKey]['credits'];
                        $usedCourses[$courseKey] = true;
                    } else {
                        $availableChoiceCourses[] = [
                            'subject' => $course['subject'],
                            'number_code' => $course['number_code'],
                            'title' => $course['title'],
                            'credits' => $course['credits'],
                            'completed' => false
                        ];
                    }
                }
            }

            // Completed courses are displayed in fixed_courses
            foreach ($completedChoiceCourses as $course) {
                $category['fixed_courses'][] = $course;
                $category['total_count']++;
                $category['completed_count']++;
            }

            // Available courses are displayed in choice_courses
            if (!empty($availableChoiceCourses)) {
                $choiceGroup['courses'] = $availableChoiceCourses;
                $category['choice_courses'][] = $choiceGroup;
            }
                
                $category['choice_courses'][] = $choiceGroup;
            }
        }
        
        $result['categories'][] = $category;
    }
    
    if (!empty($requirements['capstone'])) {
        $category = [
            'name' => 'Capstone',
            'type' => 'capstone',
            'fixed_courses' => [],
            'choice_courses' => [],
            'completed_count' => 0,
            'total_count' => 0,
            'completed_credits' => 0,
            'required_credits' => 3
        ];

        $capstoneChoices = [];
        foreach ($requirements['capstone'] as $course) {
            $courseKey = getCourseKey($course['subject'], $course['number_code']);
            $isCompleted = matchCourse($courseKey, $completedCourses);

            if ($isCompleted) {
                // Completed Capstone courses are displayed directly in fixed_courses
                $category['fixed_courses'][] = [
                    'subject' => $course['subject'],
                    'number_code' => $course['number_code'],
                    'title' => $course['title'],
                    'credits' => $course['credits'],
                    'completed' => true
                ];
                $category['completed_credits'] += $completedCourses[$courseKey]['credits'];
                $category['completed_count']++;
                $category['total_count']++;
                $usedCourses[$courseKey] = true;
            } else {
                // Available Capstone courses are placed in choice_courses
                $capstoneChoices[] = [
                    'subject' => $course['subject'],
                    'number_code' => $course['number_code'],
                    'title' => $course['title'],
                    'credits' => $course['credits'],
                    'completed' => false
                ];
            }
        }

        // Only add choice_courses when there are available Capstone courses
        if (!empty($capstoneChoices)) {
            $category['choice_courses'][] = [
                'label' => 'Choose one capstone course',
                'courses' => $capstoneChoices
            ];
        }

        $result['categories'][] = $category;
    }

    if (!empty($requirements['major_electives'])) {
        foreach ($requirements['major_electives'] as $elective) {
            $category = [
                'name' => 'Major Electives',
                'type' => 'major_electives',
                'rule' => [
                    'min_credits' => $elective['min_credits'],
                    'min_level' => $elective['min_level'],
                    'allowed_subjects' => $elective['subjects']
                ],
                'fixed_courses' => [],
                'choice_courses' => [],
                'completed_count' => 0,
                'total_count' => 0,
                'completed_credits' => 0,
                'required_credits' => $elective['min_credits']
            ];

            $allowedSubjects = array_map('trim', explode(',', $elective['subjects']));
            $minLevel = intval($elective['min_level']);

            foreach ($completedCourses as $courseCode => $courseInfo) {
                if (isset($usedCourses[$courseCode])) {
                    continue;
                }

                $parts = explode(' ', $courseCode);
                if (count($parts) >= 2) {
                    $subject = $parts[0];
                    $numberCode = $parts[1];
                    $courseLevel = intval($numberCode);

                    if (in_array($subject, $allowedSubjects) && $courseLevel >= $minLevel) {
                        $category['fixed_courses'][] = [
                            'subject' => $subject,
                            'number_code' => $numberCode,
                            'title' => $courseInfo['name'],
                            'credits' => $courseInfo['credits'],
                            'completed' => true
                        ];

                        $category['completed_count']++;
                        $category['completed_credits'] += $courseInfo['credits'];
                        $usedCourses[$courseCode] = true;
                    }
                }
            }

            $result['categories'][] = $category;
        }
    }
    
    if (!empty($requirements['free_electives'])) {
        foreach ($requirements['free_electives'] as $elective) {
            $category = [
                'name' => 'Free Electives - ' . $elective['name'],
                'type' => 'free_electives',
                'rule' => [
                    'min_credits' => $elective['min_credits'],
                    'max_credits' => $elective['max_credits'],
                    'upper_division_min_pct' => $elective['upper_division_min_pct']
                ],
                'fixed_courses' => [],
                'choice_courses' => [],
                'completed_count' => 0,
                'total_count' => 0,
                'completed_credits' => 0,
                'required_credits' => $elective['min_credits']
            ];

            $upperDivisionCredits = 0;

            foreach ($completedCourses as $courseCode => $courseInfo) {
                if (isset($usedCourses[$courseCode])) {
                    continue;
                }

                $parts = explode(' ', $courseCode);
                if (count($parts) >= 2) {
                    $subject = $parts[0];
                    $numberCode = $parts[1];
                    $courseLevel = intval($numberCode);

                    if ($subject === 'CPS' || ($subject === 'MATH' && intval($numberCode) >= 2000)) {
                        continue;
                    }

                    $category['fixed_courses'][] = [
                        'subject' => $subject,
                        'number_code' => $numberCode,
                        'title' => $courseInfo['name'],
                        'credits' => $courseInfo['credits'],
                        'completed' => true
                    ];

                    $category['completed_count']++;
                    $category['completed_credits'] += $courseInfo['credits'];

                    if ($courseLevel >= 3000) {
                        $upperDivisionCredits += $courseInfo['credits'];
                    }
                }
            }

            if ($category['completed_credits'] > 0) {
                $category['upper_division_pct'] = round(($upperDivisionCredits / $category['completed_credits']) * 100);
                $category['upper_division_credits'] = $upperDivisionCredits;
            } else {
                $category['upper_division_pct'] = 0;
                $category['upper_division_credits'] = 0;
            }

            $result['categories'][] = $category;
        }
    }

    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    
    $programCode = isset($_GET['code']) ? trim($_GET['code']) : 'BS-CPS';
    
    try {
        $pdo = getDBConnection();
        $result = getProgramRequirements($pdo, $programCode);
        
        if (!$result) {
            http_response_code(404);
            echo json_encode(['error' => 'Program not found']);
            exit;
        }
        
        echo json_encode($result, JSON_PRETTY_PRINT);
        exit;
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error']);
        exit;
    }
}

function getProgramRequirements($pdo, $programCode) {
    $stmt = $pdo->prepare("
        SELECT program_id, code, name, total_credits_req, ge_credits_req,
               free_electives_min, free_electives_max, upper_division_min_pct_free
        FROM program
        WHERE code = ?
    ");
    $stmt->execute([$programCode]);
    $program = $stmt->fetch();
    
    if (!$program) {
        return null;
    }
    
    $result = [
        'program' => $program,
        'ge_foundation' => [],
        'ge_humanities' => [],
        'ge_social_sciences' => [],
        'ge_science_math' => [],
        'additional_required' => [],
        'major_core' => [],
        'major_concentration' => [],
        'major_electives' => [],
        'capstone' => [],
        'free_electives' => []
    ];
    
    $stmt = $pdo->prepare("
        SELECT c.subject, c.number_code, c.title, c.credits
        FROM program p
        JOIN program_ge_foundation pgf ON pgf.program_id = p.program_id
        JOIN ge_foundation_set f       ON f.found_set_id = pgf.found_set_id
        JOIN ge_foundation_course fc   ON fc.found_set_id = f.found_set_id
        JOIN course c                  ON c.course_id = fc.course_id
        WHERE p.code = ?
        ORDER BY c.subject, c.number_code
    ");
    $stmt->execute([$programCode]);
    $foundationFixed = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("
        SELECT cs.code AS choice_code, c.subject, c.number_code, c.title, c.credits
        FROM program p
        JOIN program_ge_foundation pgf      ON pgf.program_id = p.program_id
        JOIN ge_foundation_set f            ON f.found_set_id = pgf.found_set_id
        JOIN ge_foundation_choice_set cs    ON cs.found_set_id = f.found_set_id
        JOIN ge_foundation_choice_course cc ON cc.found_choice_set_id = cs.found_choice_set_id
        JOIN course c                       ON c.course_id = cc.course_id
        WHERE p.code = ? AND cs.code = 'FOUNDATION_TRANSITION'
        ORDER BY c.subject, c.number_code
    ");
    $stmt->execute([$programCode]);
    $foundationTransition = $stmt->fetchAll();
    
    $result['ge_foundation'] = [
        'fixed' => $foundationFixed,
        'transition_choices' => $foundationTransition
    ];
    
    $stmt = $pdo->prepare("
        SELECT c.subject, c.number_code, c.title, c.credits
        FROM program p
        JOIN program_ge_hum pgh ON pgh.program_id = p.program_id
        JOIN ge_hum_set s       ON s.hum_set_id = pgh.hum_set_id
        JOIN ge_hum_course hc   ON hc.hum_set_id = s.hum_set_id
        JOIN course c           ON c.course_id = hc.course_id
        WHERE p.code = ?
        ORDER BY c.subject, c.number_code
    ");
    $stmt->execute([$programCode]);
    $humFixed = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("
        SELECT cs.hum_choice_set_id, cs.code, cs.label, cs.select_min_count, cs.select_min_credits
        FROM program p
        JOIN program_ge_hum pgh  ON pgh.program_id = p.program_id
        JOIN ge_hum_set s        ON s.hum_set_id = pgh.hum_set_id
        JOIN ge_hum_choice_set cs ON cs.hum_set_id = s.hum_set_id
        WHERE p.code = ? AND cs.code = 'GE_HUM_ELECTIVE'
        LIMIT 1
    ");
    $stmt->execute([$programCode]);
    $humChoiceMeta = $stmt->fetch();
    
    $humAreas = [];
    if ($humChoiceMeta) {
        $stmt = $pdo->prepare("
            SELECT ca.area_label
            FROM ge_hum_choice_area ca
            WHERE ca.hum_choice_set_id = ?
            ORDER BY ca.area_label
        ");
        $stmt->execute([$humChoiceMeta['hum_choice_set_id']]);
        $humAreas = $stmt->fetchAll();
    }
    
    $result['ge_humanities'] = [
        'fixed' => $humFixed,
        'choice_meta' => $humChoiceMeta,
        'areas' => $humAreas
    ];
    
    $stmt = $pdo->prepare("
        SELECT c.subject, c.number_code, c.title, c.credits
        FROM program p
        JOIN program_ge_soc pgs ON pgs.program_id = p.program_id
        JOIN ge_soc_set s       ON s.soc_set_id   = pgs.soc_set_id
        JOIN ge_soc_course sc   ON sc.soc_set_id  = s.soc_set_id
        JOIN course c           ON c.course_id    = sc.course_id
        WHERE p.code = ?
        ORDER BY c.subject, c.number_code
    ");
    $stmt->execute([$programCode]);
    $socFixed = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("
        SELECT cs.soc_choice_set_id, cs.code, cs.label, cs.select_min_count, cs.select_min_credits
        FROM program p
        JOIN program_ge_soc pgs   ON pgs.program_id = p.program_id
        JOIN ge_soc_set s         ON s.soc_set_id   = pgs.soc_set_id
        JOIN ge_soc_choice_set cs ON cs.soc_set_id  = s.soc_set_id
        WHERE p.code = ? AND cs.code = 'GE_SOCSCI_ELECTIVE'
        LIMIT 1
    ");
    $stmt->execute([$programCode]);
    $socChoiceMeta = $stmt->fetch();
    
    $socAreas = [];
    if ($socChoiceMeta) {
        $stmt = $pdo->prepare("
            SELECT ca.area_label
            FROM ge_soc_choice_area ca
            WHERE ca.soc_choice_set_id = ?
            ORDER BY ca.area_label
        ");
        $stmt->execute([$socChoiceMeta['soc_choice_set_id']]);
        $socAreas = $stmt->fetchAll();
    }
    
    $result['ge_social_sciences'] = [
        'fixed' => $socFixed,
        'choice_meta' => $socChoiceMeta,
        'areas' => $socAreas
    ];
    
    $stmt = $pdo->prepare("
        SELECT s.sm_set_id, s.name, s.min_credits
        FROM program p
        JOIN program_ge_scimath pgs ON pgs.program_id = p.program_id
        JOIN ge_scimath_set s       ON s.sm_set_id    = pgs.sm_set_id
        WHERE p.code = ?
        LIMIT 1
    ");
    $stmt->execute([$programCode]);
    $sciMathMeta = $stmt->fetch();
    
    $stmt = $pdo->prepare("
        SELECT c.subject, c.number_code, c.title, c.credits
        FROM program p
        JOIN program_ge_scimath pgs ON pgs.program_id = p.program_id
        JOIN ge_scimath_set s       ON s.sm_set_id    = pgs.sm_set_id
        JOIN ge_scimath_course smc  ON smc.sm_set_id  = s.sm_set_id
        JOIN course c               ON c.course_id    = smc.course_id
        WHERE p.code = ?
        ORDER BY c.subject, c.number_code
    ");
    $stmt->execute([$programCode]);
    $sciMathFixed = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("
        SELECT cs.code AS choice_code, c.subject, c.number_code, c.title, c.credits
        FROM program p
        JOIN program_ge_scimath pgs      ON pgs.program_id = p.program_id
        JOIN ge_scimath_set s            ON s.sm_set_id    = pgs.sm_set_id
        JOIN ge_scimath_choice_set cs    ON cs.sm_set_id   = s.sm_set_id
        JOIN ge_scimath_choice_course cc ON cc.sm_choice_set_id = cs.sm_choice_set_id
        JOIN course c                    ON c.course_id    = cc.course_id
        WHERE p.code = ? AND cs.code = 'LAB_SCIENCE_I'
        ORDER BY c.subject, c.number_code
    ");
    $stmt->execute([$programCode]);
    $labSciI = $stmt->fetchAll();
    
    $result['ge_science_math'] = [
        'meta' => $sciMathMeta,
        'fixed' => $sciMathFixed,
        'lab_science_i' => $labSciI
    ];
    
    $stmt = $pdo->prepare("
        SELECT s.add_set_id, s.name, s.min_credits
        FROM program p
        JOIN program_additional_required par ON par.program_id = p.program_id
        JOIN additional_required_set s       ON s.add_set_id   = par.add_set_id
        WHERE p.code = ?
        ORDER BY s.add_set_id
    ");
    $stmt->execute([$programCode]);
    $addSetMeta = $stmt->fetchAll();
    
    $addFixedBySet = [];
    $stmt = $pdo->prepare("
        SELECT s.add_set_id, c.subject, c.number_code, c.title, c.credits, i.min_grade
        FROM program p
        JOIN program_additional_required par ON par.program_id = p.program_id
        JOIN additional_required_set s       ON s.add_set_id   = par.add_set_id
        JOIN additional_required_item i      ON i.add_set_id   = s.add_set_id
        JOIN course c                        ON c.course_id    = i.course_id
        WHERE p.code = ? AND i.item_type = 'COURSE'
        ORDER BY s.add_set_id, c.subject, c.number_code
    ");
    $stmt->execute([$programCode]);
    foreach ($stmt->fetchAll() as $r) {
        $addFixedBySet[$r['add_set_id']][] = $r;
    }
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            s.add_set_id,
            cs.add_choice_set_id,
            cs.code,
            cs.label,
            cs.select_min_count,
            cs.select_min_credits,
            cs.each_min_credits,
            cs.each_max_credits,
            cs.min_level,
            cs.min_level_subject
        FROM program p
        JOIN program_additional_required par ON par.program_id = p.program_id
        JOIN additional_required_set s       ON s.add_set_id   = par.add_set_id
        JOIN additional_required_item i      ON i.add_set_id   = s.add_set_id
        JOIN additional_choice_set cs        ON cs.add_choice_set_id = i.add_choice_set_id
        WHERE p.code = ? AND i.item_type = 'CHOICE'
        ORDER BY s.add_set_id, cs.add_choice_set_id
    ");
    $stmt->execute([$programCode]);
    $choiceMetaRows = $stmt->fetchAll();
    
    $addChoiceMetaBySet = [];
    $addChoiceCoursesByChoiceId = [];
    $addChoiceSubjectsByChoiceId = [];
    $choiceIds = [];
    
    foreach ($choiceMetaRows as $r) {
        $addChoiceMetaBySet[$r['add_set_id']][] = $r;
        $choiceIds[] = (int)$r['add_choice_set_id'];
    }
    
    if (!empty($choiceIds)) {
        $choiceIds = array_values(array_unique($choiceIds));
        $in = implode(',', array_fill(0, count($choiceIds), '?'));
        
        $stmt = $pdo->prepare("
            SELECT cc.add_choice_set_id, c.subject, c.number_code, c.title, c.credits
            FROM additional_choice_course cc
            JOIN course c ON c.course_id = cc.course_id
            WHERE cc.add_choice_set_id IN ($in)
            ORDER BY c.subject, c.number_code
        ");
        $stmt->execute($choiceIds);
        foreach ($stmt->fetchAll() as $r) {
            $addChoiceCoursesByChoiceId[(int)$r['add_choice_set_id']][] = $r;
        }
        
        $stmt = $pdo->prepare("
            SELECT acs.add_choice_set_id, acs.subject
            FROM additional_choice_subject acs
            WHERE acs.add_choice_set_id IN ($in)
            ORDER BY acs.subject
        ");
        $stmt->execute($choiceIds);
        foreach ($stmt->fetchAll() as $r) {
            $addChoiceSubjectsByChoiceId[(int)$r['add_choice_set_id']][] = $r;
        }
    }
    
    $result['additional_required'] = [
        'sets' => $addSetMeta,
        'fixed_by_set' => $addFixedBySet,
        'choice_meta_by_set' => $addChoiceMetaBySet,
        'choice_courses_by_id' => $addChoiceCoursesByChoiceId,
        'choice_subjects_by_id' => $addChoiceSubjectsByChoiceId
    ];
    
    $stmt = $pdo->prepare("
        SELECT s.core_set_id, s.name, s.min_credits
        FROM program p
        JOIN program_major_core pmc ON pmc.program_id = p.program_id
        JOIN major_core_set s       ON s.core_set_id  = pmc.core_set_id
        WHERE p.code = ?
        ORDER BY s.core_set_id
    ");
    $stmt->execute([$programCode]);
    $coreMeta = $stmt->fetchAll();
    
    $coreBySet = [];
    $stmt = $pdo->prepare("
        SELECT s.core_set_id, c.subject, c.number_code, c.title, c.credits, mcc.min_grade
        FROM program p
        JOIN program_major_core pmc ON pmc.program_id = p.program_id
        JOIN major_core_set s       ON s.core_set_id  = pmc.core_set_id
        JOIN major_core_course mcc  ON mcc.core_set_id = s.core_set_id
        JOIN course c               ON c.course_id = mcc.course_id
        WHERE p.code = ?
        ORDER BY s.core_set_id, c.subject, c.number_code
    ");
    $stmt->execute([$programCode]);
    foreach ($stmt->fetchAll() as $r) {
        $coreBySet[$r['core_set_id']][] = $r;
    }
    
    $result['major_core'] = [
        'sets' => $coreMeta,
        'courses_by_set' => $coreBySet
    ];
    
    $stmt = $pdo->prepare("
        SELECT s.conc_set_id, s.name, s.min_credits
        FROM program p
        JOIN program_major_concentration pmc ON pmc.program_id = p.program_id
        JOIN major_concentration_set s       ON s.conc_set_id  = pmc.conc_set_id
        WHERE p.code = ?
        ORDER BY s.conc_set_id
    ");
    $stmt->execute([$programCode]);
    $concSetMeta = $stmt->fetchAll();
    
    $concFixedBySet = [];
    $stmt = $pdo->prepare("
        SELECT s.conc_set_id, c.subject, c.number_code, c.title, c.credits, i.min_grade
        FROM program p
        JOIN program_major_concentration pmc ON pmc.program_id = p.program_id
        JOIN major_concentration_set s       ON s.conc_set_id  = pmc.conc_set_id
        JOIN major_concentration_item i      ON i.conc_set_id  = s.conc_set_id
        JOIN course c                        ON c.course_id    = i.course_id
        WHERE p.code = ? AND i.item_type = 'COURSE'
        ORDER BY s.conc_set_id, i.display_order, c.subject, c.number_code
    ");
    $stmt->execute([$programCode]);
    foreach ($stmt->fetchAll() as $r) {
        $concFixedBySet[$r['conc_set_id']][] = $r;
    }
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            s.conc_set_id,
            cs.conc_choice_set_id,
            cs.code,
            cs.label,
            cs.select_min_count,
            cs.select_min_credits
        FROM program p
        JOIN program_major_concentration pmc ON pmc.program_id = p.program_id
        JOIN major_concentration_set s       ON s.conc_set_id  = pmc.conc_set_id
        JOIN major_concentration_item i      ON i.conc_set_id  = s.conc_set_id
        JOIN major_concentration_choice_set cs
             ON cs.conc_choice_set_id = i.conc_choice_set_id
        WHERE p.code = ? AND i.item_type = 'CHOICE'
        ORDER BY s.conc_set_id, cs.conc_choice_set_id
    ");
    $stmt->execute([$programCode]);
    $choiceMetaRows = $stmt->fetchAll();
    
    $concChoiceMetaBySet = [];
    $concChoiceCoursesByChoiceId = [];
    $choiceIds = [];
    
    foreach ($choiceMetaRows as $r) {
        $concChoiceMetaBySet[$r['conc_set_id']][] = $r;
        $choiceIds[] = (int)$r['conc_choice_set_id'];
    }
    
    if (!empty($choiceIds)) {
        $choiceIds = array_values(array_unique($choiceIds));
        $in = implode(',', array_fill(0, count($choiceIds), '?'));
        $stmt = $pdo->prepare("
            SELECT cc.conc_choice_set_id, c.subject, c.number_code, c.title, c.credits
            FROM major_concentration_choice_course cc
            JOIN course c ON c.course_id = cc.course_id
            WHERE cc.conc_choice_set_id IN ($in)
            ORDER BY c.subject, c.number_code
        ");
        $stmt->execute($choiceIds);
        foreach ($stmt->fetchAll() as $r) {
            $concChoiceCoursesByChoiceId[(int)$r['conc_choice_set_id']][] = $r;
        }
    }
    
    $result['major_concentration'] = [
        'sets' => $concSetMeta,
        'fixed_by_set' => $concFixedBySet,
        'choice_meta_by_set' => $concChoiceMetaBySet,
        'choice_courses_by_id' => $concChoiceCoursesByChoiceId
    ];
    
    $stmt = $pdo->prepare("
        SELECT me.min_credits, me.min_level,
               GROUP_CONCAT(ms.subject ORDER BY ms.subject SEPARATOR ', ') AS subjects
        FROM program p
        JOIN program_major_electives pme ON pme.program_id = p.program_id
        JOIN major_electives_req me      ON me.me_req_id   = pme.me_req_id
        LEFT JOIN me_allowed_subject ms  ON ms.me_req_id   = me.me_req_id
        WHERE p.code = ?
        GROUP BY me.me_req_id, me.min_credits, me.min_level
    ");
    $stmt->execute([$programCode]);
    $result['major_electives'] = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("
        SELECT c.subject, c.number_code, c.title, c.credits
        FROM program p
        JOIN program_capstone pc ON pc.program_id = p.program_id
        JOIN capstone_set cs     ON cs.cap_set_id  = pc.cap_set_id
        JOIN capstone_course cc  ON cc.cap_set_id  = cs.cap_set_id
        JOIN course c            ON c.course_id    = cc.course_id
        WHERE p.code = ?
        ORDER BY c.subject, c.number_code
    ");
    $stmt->execute([$programCode]);
    $result['capstone'] = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("
        SELECT fe.name, fe.min_credits, fe.max_credits, fe.upper_division_min_pct
        FROM program p
        JOIN program_free_electives pfe ON pfe.program_id = p.program_id
        JOIN free_electives_req fe      ON fe.fe_req_id   = pfe.fe_req_id
        WHERE p.code = ?
    ");
    $stmt->execute([$programCode]);
    $result['free_electives'] = $stmt->fetchAll();
    
    return $result;
}

header('Content-Type: text/html; charset=utf-8');
$programCode = isset($_GET['code']) ? trim($_GET['code']) : 'BS-CPS';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>MySQL Test - <?php echo htmlspecialchars($programCode); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
 body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;line-height:1.35;margin:24px;}
 h1{font-size:20px;margin:0 0 12px;}
 h2{font-size:16px;margin:18px 0 8px;border-bottom:1px solid #eee;padding-bottom:4px;}
 h3{font-size:14px;margin:12px 0 6px;}
 code,pre{background:#f7f7f7;padding:2px 6px;border-radius:4px}
 table{border-collapse:collapse;width:100%;max-width:1100px;margin:6px 0 16px;} 
 th,td{border:1px solid #e5e5e5;padding:6px 8px;text-align:left;vertical-align:top}
 th{background:#fafafa}
 .ok{color:#0a7c2f}.warn{color:#b76e00}.bad{color:#b00020}
 .muted{color:#666}
 .pill{display:inline-block;padding:2px 8px;border-radius:999px;background:#eee;font-size:12px}
 .row{margin:6px 0}
</style>
</head>
<body>
  <h1>MySQL Connectivity & Data Check</h1>
  <div class="row">API Endpoint Active - Use ?format=json for JSON output or POST for course comparison</div>
  <form method="get" class="row">
    <label>Program code:
      <input name="code" value="<?php echo htmlspecialchars($programCode); ?>" />
    </label>
    <button type="submit">Load</button>
  </form>
  <p class="muted">API Usage:</p>
  <ul class="muted">
    <li>GET request with ?code=BS-CPS&format=json to retrieve program requirements</li>
    <li>POST request with JSON body containing program_code and courses array for course comparison</li>
  </ul>
</body>
</html>





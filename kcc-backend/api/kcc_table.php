<?php
/***********************
 * Minimal MySQL tester (full page)
 * Shows GE (Foundation/Humanities/Social Sci/Sci&Math),
 * Major Core, Major Concentration (fixed + choice tables),
 * Major Electives rule, Capstone.
 ***********************/
ini_set('display_errors', 1);
error_reporting(E_ALL);

$dbHost = 'imc.kean.edu';          // server
$dbName = '2025F_CPS4301_01';      // schema
$dbUser = '2025F_CPS4301_01';      // user
$dbPass = '2025F_CPS4301_01';      // password

$programCode = isset($_GET['code']) && $_GET['code'] !== '' ? $_GET['code'] : 'BS-CPS';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

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

  // ---------- Basic info ----------
  $ver = $pdo->query("SELECT VERSION() AS v")->fetch()['v'] ?? 'unknown';
  $now = $pdo->query("SELECT NOW() AS n")->fetch()['n'] ?? '';
  $dbs = $pdo->query("SELECT DATABASE() AS d")->fetch()['d'] ?? '';

  // ---------- Tables present ----------
  $tables = $pdo->query("
    SELECT table_name
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
    ORDER BY table_name
  ")->fetchAll();

  // ---------- Program row ----------
  $stmt = $pdo->prepare("
    SELECT program_id, code, name, total_credits_req, ge_credits_req,
           free_electives_min, free_electives_max, upper_division_min_pct_free
    FROM program
    WHERE code = ?
  ");
  $stmt->execute([$programCode]);
  $program = $stmt->fetch();

  // ---------- Course count ----------
  $courseCount = $pdo->query("SELECT COUNT(*) AS c FROM course")->fetch()['c'] ?? 0;

  // =========================================================
  // GE — Foundation
  // =========================================================

  // Foundation fixed (ENG 1030, MATH 1054, COMM 1402, GE 2024)
  $foundation = [];
  if ($program) {
    $stmt = $pdo->prepare("
      SELECT c.subject, c.number_code, c.title, c.credits
      FROM program p
      JOIN program_ge_foundation pgf ON pgf.program_id = p.program_id
      JOIN ge_foundation_set f       ON f.found_set_id = pgf.found_set_id
      JOIN ge_foundation_course fc   ON fc.found_set_id = f.found_set_id
      JOIN course c                  ON c.course_id    = fc.course_id
      WHERE p.code = ?
      ORDER BY c.subject, c.number_code
    ");
    $stmt->execute([$programCode]);
    $foundation = $stmt->fetchAll();
  }

  // Foundation Transition choice (GE 1000 or GE 3000)
  $foundationTransition = [];
  if ($program) {
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
  }

  // =========================================================
  // GE — Humanities
  // =========================================================

  // Humanities fixed (ENG 2403)
  $humFixed = [];
  if ($program) {
    $stmt = $pdo->prepare("
      SELECT c.subject, c.number_code, c.title, c.credits
      FROM program p
      JOIN program_ge_hum pgh ON pgh.program_id = p.program_id
      JOIN ge_hum_set s       ON s.hum_set_id   = pgh.hum_set_id
      JOIN ge_hum_course hc   ON hc.hum_set_id  = s.hum_set_id
      JOIN course c           ON c.course_id    = hc.course_id
      WHERE p.code = ?
      ORDER BY c.subject, c.number_code
    ");
    $stmt->execute([$programCode]);
    $humFixed = $stmt->fetchAll();
  }

  // Humanities elective (areas)
  $humAreas = [];
  $humChoiceMeta = null;
  if ($program) {
    $stmt = $pdo->prepare("
      SELECT cs.hum_choice_set_id, cs.code, cs.label, cs.select_min_count, cs.select_min_credits
      FROM program p
      JOIN program_ge_hum pgh   ON pgh.program_id = p.program_id
      JOIN ge_hum_set s         ON s.hum_set_id   = pgh.hum_set_id
      JOIN ge_hum_choice_set cs ON cs.hum_set_id  = s.hum_set_id
      WHERE p.code = ? AND cs.code = 'GE_HUM_ELECTIVE'
      LIMIT 1
    ");
    $stmt->execute([$programCode]);
    $humChoiceMeta = $stmt->fetch();

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
  }

  // =========================================================
  // GE — Social Sciences
  // =========================================================

  // Social Sciences fixed (HIST 1062)
  $socFixed = [];
  if ($program) {
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
  }

  // Social Sciences elective (areas)
  $socAreas = [];
  $socChoiceMeta = null;
  if ($program) {
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
  }

  // =========================================================
  // GE — Science & Mathematics
  // =========================================================

  // Sci&Math meta + fixed (e.g., CPS 1231)
  $sciMathMeta  = null;
  $sciMathFixed = [];
  if ($program) {
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
  }

  // Lab Science I choices
  $labSciI = [];
  if ($program) {
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
  }

  // ======================
  // Additional Required
  // ======================
  $addSetMeta = [];
  $addFixedBySet = [];
  $addChoiceMetaBySet = [];
  $addChoiceCoursesByChoiceId = [];
  $addChoiceSubjectsByChoiceId = [];

  if ($program) {
    // Sets
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

    // Fixed course items
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

    // Choice set meta (CALCII_OR_LINEARALG, LAB_SCIENCE_II, MATH_ELECTIVE_APPROVED, TWO_ELECTIVES_MATH_OR_SCI, etc.)
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

    $choiceIds = [];
    foreach ($choiceMetaRows as $r) {
      $addChoiceMetaBySet[$r['add_set_id']][] = $r;
      $choiceIds[] = (int)$r['add_choice_set_id'];
    }

    if (!empty($choiceIds)) {
      $choiceIds = array_values(array_unique($choiceIds));
      $in = implode(',', array_fill(0, count($choiceIds), '?'));

      // Courses under choice sets
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

      // Subject-based choice sets (e.g., TWO_ELECTIVES_MATH_OR_SCI)
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
  }

  // =========================================================
  // Major — Core
  // =========================================================

  $coreMeta = [];
  $coreRows = [];
  $coreBySet = [];

  if ($program) {
    // core sets
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

    // core courses
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
    $coreRows = $stmt->fetchAll();

    foreach ($coreRows as $r) {
      $coreBySet[$r['core_set_id']][] = $r;
    }
  }

  // =========================================================
  // Major — Concentration (fixed + choice tables)
  // =========================================================

  $concSetMeta = [];
  $concFixedBySet = [];
  $concChoiceMetaBySet = [];
  $concChoiceCoursesByChoiceId = [];

  if ($program) {
    // sets
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

    // fixed courses
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

    // choice meta
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

    $choiceIds = [];
    foreach ($choiceMetaRows as $r) {
      $concChoiceMetaBySet[$r['conc_set_id']][] = $r;
      $choiceIds[] = (int)$r['conc_choice_set_id'];
    }

    // choice courses
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
  }

  // =========================================================
  // Major — Electives rule snapshot
  // =========================================================
  $majorElectives = [];
  if ($program) {
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
    $majorElectives = $stmt->fetchAll();
  }

  // =========================================================
  // Capstone options
  // =========================================================
  $capstones = [];
  if ($program) {
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
    $capstones = $stmt->fetchAll();
  }

  // ======================
  // Free Electives (rule)
  // ======================
  $freeElectives = [];
  if ($program) {
    $stmt = $pdo->prepare("
      SELECT fe.name, fe.min_credits, fe.max_credits, fe.upper_division_min_pct
      FROM program p
      JOIN program_free_electives pfe ON pfe.program_id = p.program_id
      JOIN free_electives_req fe      ON fe.fe_req_id   = pfe.fe_req_id
      WHERE p.code = ?
    ");
    $stmt->execute([$programCode]);
    $freeElectives = $stmt->fetchAll();
  }

} catch (Throwable $e) {
  http_response_code(500);
  echo "<pre style='color:#b00'>DB error: " . h($e->getMessage()) . "</pre>";
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>MySQL Test — <?=h($programCode)?></title>
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

  <div class="row">Connected to <b><?=h($dbs)?></b> • MySQL <b><?=h($ver)?></b> • Server time <b><?=h($now)?></b></div>

  <form method="get" class="row">
    <label>Program code:
      <input name="code" value="<?=h($programCode)?>" />
    </label>
    <button type="submit">Load</button>
  </form>

  <h2>Tables present (<?=count($tables)?>)</h2>
  <?php if (!$tables): ?>
    <div class="bad">No tables found in this schema.</div>
  <?php else: ?>
    <div class="muted"><?=implode(', ', array_map(fn($r)=>h($r['table_name']), $tables))?></div>
  <?php endif; ?>

  <h2>Program</h2>
  <?php if (!$program): ?>
    <div class="bad">Program <code><?=h($programCode)?></code> not found.</div>
  <?php else: ?>
    <table>
      <tr><th>Code</th><th>Name</th><th>Total cr</th><th>GE cr</th><th>Free Min</th><th>Free Max</th><th>Free ≥3000 %</th></tr>
      <tr>
        <td><?=h($program['code'])?></td>
        <td><?=h($program['name'])?></td>
        <td><?=h($program['total_credits_req'])?></td>
        <td><?=h($program['ge_credits_req'])?></td>
        <td><?=h($program['free_electives_min'])?></td>
        <td><?=h($program['free_electives_max'])?></td>
        <td><?=h($program['upper_division_min_pct_free'])?>%</td>
      </tr>
    </table>
  <?php endif; ?>

  <h2>Course catalog count</h2>
  <div><span class="pill">course</span> rows: <b><?=$courseCount?></b></div>

  <!-- GE — Foundation -->
  <h2>GE — Foundation (fixed courses)</h2>
  <?php if (!$foundation): ?>
    <div class="warn">No foundation courses mapped for <?=h($programCode)?>.</div>
  <?php else: ?>
    <table>
      <tr><th>Subject</th><th>Number</th><th>Title</th><th>Credits</th></tr>
      <?php foreach ($foundation as $r): ?>
        <tr>
          <td><?=h($r['subject'])?></td>
          <td><?=h($r['number_code'])?></td>
          <td><?=h($r['title'])?></td>
          <td><?=h($r['credits'])?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <h2>GE — Foundation Transition (choice set)</h2>
  <?php if (!$foundationTransition): ?>
    <div class="warn">No Foundation Transition choices found for <?=h($programCode)?>.</div>
  <?php else: ?>
    <table>
      <tr><th>Choice</th><th>Subject</th><th>Number</th><th>Title</th><th>Credits</th></tr>
      <?php foreach ($foundationTransition as $r): ?>
        <tr>
          <td><?=h($r['choice_code'])?></td>
          <td><?=h($r['subject'])?></td>
          <td><?=h($r['number_code'])?></td>
          <td><?=h($r['title'])?></td>
          <td><?=h($r['credits'])?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <!-- GE — Humanities -->
  <h2>GE — Humanities (fixed courses)</h2>
  <?php if (!$humFixed): ?>
    <div class="warn">No Humanities fixed courses found for <?=h($programCode)?>.</div>
  <?php else: ?>
    <table>
      <tr><th>Subject</th><th>Number</th><th>Title</th><th>Credits</th></tr>
      <?php foreach ($humFixed as $r): ?>
        <tr>
          <td><?=h($r['subject'])?></td>
          <td><?=h($r['number_code'])?></td>
          <td><?=h($r['title'])?></td>
          <td><?=h($r['credits'])?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <h2>GE — Humanities elective (choice set)</h2>
  <?php if (!$humChoiceMeta): ?>
    <div class="warn">No Humanities elective choice set found for <?=h($programCode)?>.</div>
  <?php else: ?>
    <div class="muted">Requirement: choose <?=h($humChoiceMeta['select_min_count'])?> from the approved areas.</div>
    <?php if (!$humAreas): ?>
      <div class="warn">No Humanities areas defined for this choice set.</div>
    <?php else: ?>
      <table>
        <tr><th>Choice code</th><th>Area</th></tr>
        <?php foreach ($humAreas as $a): ?>
          <tr><td><?=h($humChoiceMeta['code'])?></td><td><?=h($a['area_label'])?></td></tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  <?php endif; ?>

  <!-- GE — Social Sciences -->
  <h2>GE — Social Sciences (fixed courses)</h2>
  <?php if (!$socFixed): ?>
    <div class="warn">No Social Sciences fixed courses found for <?=h($programCode)?>.</div>
  <?php else: ?>
    <table>
      <tr><th>Subject</th><th>Number</th><th>Title</th><th>Credits</th></tr>
      <?php foreach ($socFixed as $r): ?>
        <tr>
          <td><?=h($r['subject'])?></td>
          <td><?=h($r['number_code'])?></td>
          <td><?=h($r['title'])?></td>
          <td><?=h($r['credits'])?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <h2>GE — Social Sciences elective (choice set)</h2>
  <?php if (!$socChoiceMeta): ?>
    <div class="warn">No Social Sciences elective choice set found for <?=h($programCode)?>.</div>
  <?php else: ?>
    <div class="muted">Requirement: choose <?=h($socChoiceMeta['select_min_count'])?> from the approved areas.</div>
    <?php if (!$socAreas): ?>
      <div class="warn">No Social Sciences areas defined for this choice set.</div>
    <?php else: ?>
      <table>
        <tr><th>Choice code</th><th>Area</th></tr>
        <?php foreach ($socAreas as $a): ?>
          <tr><td><?=h($socChoiceMeta['code'])?></td><td><?=h($a['area_label'])?></td></tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  <?php endif; ?>

  <!-- GE — Science & Mathematics -->
  <h2>GE — Science &amp; Mathematics</h2>
  <?php if (!$sciMathMeta): ?>
    <div class="warn">No Science &amp; Mathematics set found for <?=h($programCode)?>.</div>
  <?php else: ?>
    <div class="muted">
      Set: <b><?=h($sciMathMeta['name'])?></b> • Minimum credits required: <b><?=h($sciMathMeta['min_credits'])?></b>
    </div>

    <h3>Fixed courses</h3>
    <?php if (!$sciMathFixed): ?>
      <div class="warn">No fixed courses mapped in Science &amp; Mathematics.</div>
    <?php else: ?>
      <table>
        <tr><th>Subject</th><th>Number</th><th>Title</th><th>Credits</th></tr>
        <?php foreach ($sciMathFixed as $r): ?>
          <tr>
            <td><?=h($r['subject'])?></td>
            <td><?=h($r['number_code'])?></td>
            <td><?=h($r['title'])?></td>
            <td><?=h($r['credits'])?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>

    <h3>Lab Science I (choice set)</h3>
    <?php if (!$labSciI): ?>
      <div class="warn">No Lab Science I choices found for <?=h($programCode)?>.</div>
    <?php else: ?>
      <table>
        <tr><th>Choice</th><th>Subject</th><th>Number</th><th>Title</th><th>Credits</th></tr>
        <?php foreach ($labSciI as $r): ?>
          <tr>
            <td><?=h($r['choice_code'])?></td>
            <td><?=h($r['subject'])?></td>
            <td><?=h($r['number_code'])?></td>
            <td><?=h($r['title'])?></td>
            <td><?=h($r['credits'])?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Additional Required -->
  <h2>Additional Required</h2>
  <?php if (!$addSetMeta): ?>
    <div class="warn">No Additional Required set found for <?=h($programCode)?>.</div>
  <?php else: ?>
    <?php foreach ($addSetMeta as $meta): ?>
      <div class="muted" style="margin-top:8px;">
        Set: <b><?=h($meta['name'])?></b> • Minimum credits required: <b><?=h($meta['min_credits'])?></b>
      </div>

      <h3>Fixed courses</h3>
      <?php $fixed = $addFixedBySet[$meta['add_set_id']] ?? []; ?>
      <?php if (!$fixed): ?>
        <div class="warn">No fixed courses in this set.</div>
      <?php else: ?>
        <table>
          <tr><th>Subject</th><th>Number</th><th>Title</th><th>Credits</th><th>Min grade</th></tr>
          <?php foreach ($fixed as $r): ?>
            <tr>
              <td><?=h($r['subject'])?></td>
              <td><?=h($r['number_code'])?></td>
              <td><?=h($r['title'])?></td>
              <td><?=h($r['credits'])?></td>
              <td><?=h($r['min_grade'])?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>

      <?php $choiceMeta = $addChoiceMetaBySet[$meta['add_set_id']] ?? []; ?>
      <?php if ($choiceMeta): ?>
        <?php foreach ($choiceMeta as $cs): ?>
          <h3>Choice: <?=h($cs['label'])?></h3>
          <div class="muted">
            Code: <b><?=h($cs['code'])?></b>
            <?php if (!empty($cs['select_min_count'])): ?> • pick <?=h($cs['select_min_count'])?><?php endif; ?>
            <?php if (!empty($cs['select_min_credits'])): ?> • min credits: <?=h($cs['select_min_credits'])?><?php endif; ?>
            <?php if (!empty($cs['each_min_credits']) || !empty($cs['each_max_credits'])): ?>
              • each <?=h($cs['each_min_credits'] ?? '')?>–<?=h($cs['each_max_credits'] ?? '')?> cr
            <?php endif; ?>
            <?php if (!empty($cs['min_level']) && !empty($cs['min_level_subject'])): ?>
              • <?=h($cs['min_level_subject'])?> ≥ <?=h($cs['min_level'])?>
            <?php endif; ?>
          </div>

          <?php $opts = $addChoiceCoursesByChoiceId[(int)$cs['add_choice_set_id']] ?? []; ?>
          <?php if ($opts): ?>
            <table>
              <tr><th>Subject</th><th>Number</th><th>Title</th><th>Credits</th></tr>
              <?php foreach ($opts as $o): ?>
                <tr>
                  <td><?=h($o['subject'])?></td>
                  <td><?=h($o['number_code'])?></td>
                  <td><?=h($o['title'])?></td>
                  <td><?=h($o['credits'])?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php endif; ?>

          <?php $subs = $addChoiceSubjectsByChoiceId[(int)$cs['add_choice_set_id']] ?? []; ?>
          <?php if ($subs): ?>
            <table>
              <tr><th>Allowed subjects</th></tr>
              <tr>
                <td>
                  <?php
                    $labels = array_map(fn($x)=>h($x['subject']), $subs);
                    echo implode(', ', $labels);
                  ?>
                </td>
              </tr>
            </table>
          <?php endif; ?>

          <?php if (!$opts && !$subs): ?>
            <div class="warn">No items defined for this choice set.</div>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="muted">No choice sets in this Additional Required set.</div>
      <?php endif; ?>

    <?php endforeach; ?>
  <?php endif; ?>

  <!-- Major — Core -->
  <h2>Major — Core</h2>
  <?php if (!$coreMeta): ?>
    <div class="warn">No Major Core set found for <?=h($programCode)?>.</div>
  <?php else: ?>
    <?php foreach ($coreMeta as $meta): ?>
      <div class="muted" style="margin-top:8px;">
        Set: <b><?=h($meta['name'])?></b> • Minimum credits required: <b><?=h($meta['min_credits'])?></b>
      </div>
      <?php $rows = $coreBySet[$meta['core_set_id']] ?? []; ?>
      <?php if (!$rows): ?>
        <div class="warn">No courses mapped in this core set.</div>
      <?php else: ?>
        <table>
          <tr><th>Subject</th><th>Number</th><th>Title</th><th>Credits</th><th>Min grade</th></tr>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?=h($r['subject'])?></td>
              <td><?=h($r['number_code'])?></td>
              <td><?=h($r['title'])?></td>
              <td><?=h($r['credits'])?></td>
              <td><?=h($r['min_grade'])?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- Major — Concentration (fixed + standalone choice tables) -->
  <h2>Major — Concentration</h2>
  <?php if (!$concSetMeta): ?>
    <div class="warn">No Major Concentration set found for <?=h($programCode)?>.</div>
  <?php else: ?>
    <?php foreach ($concSetMeta as $meta): ?>
      <div class="muted" style="margin-top:8px;">
        Set: <b><?=h($meta['name'])?></b> • Minimum credits required: <b><?=h($meta['min_credits'])?></b>
      </div>

      <h3>Fixed courses</h3>
      <?php $fixed = $concFixedBySet[$meta['conc_set_id']] ?? []; ?>
      <?php if (!$fixed): ?>
        <div class="warn">No fixed courses in this concentration set.</div>
      <?php else: ?>
        <table>
          <tr><th>Subject</th><th>Number</th><th>Title</th><th>Credits</th><th>Min grade</th></tr>
          <?php foreach ($fixed as $r): ?>
            <tr>
              <td><?=h($r['subject'])?></td>
              <td><?=h($r['number_code'])?></td>
              <td><?=h($r['title'])?></td>
              <td><?=h($r['credits'])?></td>
              <td><?=h($r['min_grade'])?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>

      <?php $choiceMeta = $concChoiceMetaBySet[$meta['conc_set_id']] ?? []; ?>
      <?php if ($choiceMeta): ?>
        <?php foreach ($choiceMeta as $cs): ?>
          <h3>Choice: <?=h($cs['label'])?></h3>
          <div class="muted">
            Code: <b><?=h($cs['code'])?></b>
            <?php if (!empty($cs['select_min_count'])): ?> • pick <?=h($cs['select_min_count'])?><?php endif; ?>
            <?php if (!empty($cs['select_min_credits'])): ?> • min credits: <?=h($cs['select_min_credits'])?><?php endif; ?>
          </div>
          <?php $opts = $concChoiceCoursesByChoiceId[(int)$cs['conc_choice_set_id']] ?? []; ?>
          <?php if (!$opts): ?>
            <div class="warn">No courses listed for this choice set.</div>
          <?php else: ?>
            <table>
              <tr><th>Subject</th><th>Number</th><th>Title</th><th>Credits</th></tr>
              <?php foreach ($opts as $o): ?>
                <tr>
                  <td><?=h($o['subject'])?></td>
                  <td><?=h($o['number_code'])?></td>
                  <td><?=h($o['title'])?></td>
                  <td><?=h($o['credits'])?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="muted">No choice sets in this concentration set.</div>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- Major — Electives rule -->
  <h2>Major — Electives (rule)</h2>
  <?php if (!$majorElectives): ?>
    <div class="warn">No major electives rule found for <?=h($programCode)?>.</div>
  <?php else: ?>
    <table>
      <tr><th>Min credits</th><th>Min level (≥)</th><th>Allowed subjects</th></tr>
      <?php foreach ($majorElectives as $r): ?>
        <tr>
          <td><?=h($r['min_credits'])?></td>
          <td><?=h($r['min_level'])?></td>
          <td><?=h($r['subjects'])?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <!-- Capstone -->
  <h2>Capstone options</h2>
  <?php if (!$capstones): ?>
    <div class="warn">No capstone options found for <?=h($programCode)?>.</div>
  <?php else: ?>
    <table>
      <tr><th>Subject</th><th>Number</th><th>Title</th><th>Credits</th></tr>
      <?php foreach ($capstones as $r): ?>
        <tr>
          <td><?=h($r['subject'])?></td>
          <td><?=h($r['number_code'])?></td>
          <td><?=h($r['title'])?></td>
          <td><?=h($r['credits'])?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <!-- Free Electives (rule) -->
  <h2>Free Electives (rule)</h2>
  <?php if (!$freeElectives): ?>
    <div class="warn">No Free Electives rule found for <?=h($programCode)?>.</div>
  <?php else: ?>
    <table>
      <tr><th>Name</th><th>Min credits</th><th>Max credits</th><th>Upper-division (≥3000) min %</th></tr>
      <?php foreach ($freeElectives as $r): ?>
        <tr>
          <td><?=h($r['name'])?></td>
          <td><?=h($r['min_credits'])?></td>
          <td><?=h($r['max_credits'])?></td>
          <td><?=h($r['upper_division_min_pct'])?>%</td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <p class="muted">Tip: switch program with <code>?code=BS-CPS</code> (and later, add your other degree codes).</p>
</body>
</html>

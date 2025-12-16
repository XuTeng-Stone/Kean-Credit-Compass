import React, { useEffect, useState, useCallback } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import '../styles/DegreeProgress.css';
import { API_BASE_URL } from '../config';

function DegreeProgress() {
  const location = useLocation();
  const navigate = useNavigate();
  const { courses, major } = location.state || {};
  const [comparisonData, setComparisonData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [expandedCategories, setExpandedCategories] = useState({});

  const fetchComparisonData = useCallback(async () => {
    setLoading(true);
    setError(null);
    
    const code = major === 'Computer Science' ? 'BS-CPS' : 'BS-IT';
    
    try {
      const res = await fetch(`${API_BASE_URL}/test.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ program_code: code, courses })
      });
      
      if (!res.ok) throw new Error('Failed to fetch data');
      
      const data = await res.json();
      setComparisonData(data);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, [courses, major]);

  useEffect(() => {
    if (!courses || !major) {
      navigate('/upload');
    } else {
      fetchComparisonData();
    }
  }, [courses, major, navigate, fetchComparisonData]);

  if (!courses || !major) {
    return null;
  }

  const totalCredits = courses.reduce((sum, course) => 
    sum + parseFloat(course['Credits'] || 0), 0
  );

  const requiredCredits = comparisonData?.program?.total_credits_req || 120;
  const remaining = Math.max(0, requiredCredits - totalCredits);
  const progress = Math.min(100, Math.round((totalCredits / requiredCredits) * 100));

  const goBack = () => navigate('/');

  const toggleCategory = (index) => {
    setExpandedCategories(prev => ({
      ...prev,
      [index]: !prev[index]
    }));
  };

  const renderCategory = (cat, idx) => {
    const progress = cat.required_credits > 0 
      ? Math.round((cat.completed_credits / cat.required_credits) * 100)
      : 0;
    
    const expanded = expandedCategories[idx];
    const isRuleBased = cat.type === 'major_electives' || cat.type === 'free_electives';
    
    const completed = cat.fixed_courses?.filter(c => c.completed) || [];
    const available = cat.fixed_courses?.filter(c => !c.completed) || [];

    return (
      <div key={idx} className="category-card">
        <div className="category-header">
          <h3 className="category-title">{cat.name}</h3>
          <div className="category-progress">
            <span className="progress-text">
              {parseInt(cat.completed_credits)} / {parseInt(cat.required_credits)} credits
            </span>
            <div className="progress-bar-small">
              <div className="progress-fill" style={{ width: `${progress}%` }}></div>
            </div>
          </div>
        </div>

        {isRuleBased && cat.rule && (
          <div className="rule-based-info">
            <div className="rule-summary">
              <h4 className="courses-subtitle">Requirements</h4>
              <div className="rule-details">
                <p>Min Credits: <strong>{parseInt(cat.rule.min_credits)}</strong></p>
                {cat.rule.min_level && (
                  <p>Min Level: <strong>{cat.rule.min_level}+</strong></p>
                )}
                {cat.rule.allowed_subjects && (
                  <p>Subjects: <strong>{cat.rule.allowed_subjects}</strong></p>
                )}
                {cat.rule.upper_division_min_pct && (
                  <p>Upper-Division: <strong>{cat.rule.upper_division_min_pct}%</strong> min (3000+)</p>
                )}
              </div>
              <div className="credits-status">
                <p className="status-text">
                  Earned: <strong>{parseInt(cat.completed_credits)}</strong> / {parseInt(cat.required_credits)}
                  {cat.completed_credits >= cat.required_credits ? 
                    <span className="status-badge completed"> Met</span> : 
                    <span className="status-badge incomplete"> {parseInt(cat.required_credits - cat.completed_credits)} needed</span>
                  }
                </p>
              </div>
            </div>
          </div>
        )}

        {isRuleBased && completed.length > 0 && (
          <div className="courses-list">
            <h4 className="courses-subtitle">Completed ({completed.length})</h4>
            {completed.map((c, i) => (
              <div key={i} className="course-item completed">
                <div className="course-info-left">
                  <span className="course-code-label">{c.subject} {c.number_code}</span>
                  <span className="course-title-label">{c.title}</span>
                </div>
                <div className="course-info-right">
                  <span className="course-credits-label">{parseInt(c.credits)} cr</span>
                  <span className="status-badge completed">Completed</span>
                </div>
              </div>
            ))}
          </div>
        )}

        {!isRuleBased && completed.length > 0 && (
          <div className="courses-list">
            <h4 className="courses-subtitle">Completed ({completed.length})</h4>
            {completed.map((c, i) => (
              <div key={i} className="course-item completed">
                <div className="course-info-left">
                  <span className="course-code-label">{c.subject} {c.number_code}</span>
                  <span className="course-title-label">{c.title}</span>
                </div>
                <div className="course-info-right">
                  <span className="course-credits-label">{parseInt(c.credits)} cr</span>
                  <span className="status-badge completed">Completed</span>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Only show expand button if category is not completed */}
        {progress < 100 && !isRuleBased && (available.length > 0 || (cat.choice_courses && cat.choice_courses.length > 0)) && (
          <div className="expand-section">
            <button className="expand-button" onClick={() => toggleCategory(idx)}>
              {expanded ? 'Hide' : `View Available (${available.length + (cat.choice_courses?.length || 0)})`}
            </button>
          </div>
        )}

        {progress < 100 && isRuleBased && cat.rule && (
          <div className="expand-section">
            <button className="expand-button" onClick={() => toggleCategory(idx)}>
              {expanded ? 'Hide' : 'View Guide'}
            </button>
          </div>
        )}

        {expanded && progress < 100 && isRuleBased && cat.rule && (
          <div className="available-courses rule-guide">
            <h4 className="courses-subtitle">Guide</h4>
            <div className="guide-content">
              {cat.type === 'major_electives' && (
                <div className="guide-section">
                  <p className="guide-text">
                    Complete <strong>{cat.rule.min_credits} credits</strong> of CPS courses at {cat.rule.min_level}+ level.
                  </p>
                  <p className="guide-text">
                    Choose any CPS {cat.rule.min_level}+ courses not already required.
                  </p>
                  <p className="guide-note">Consult your advisor for course selection.</p>
                </div>
              )}
              {cat.type === 'free_electives' && (
                <div className="guide-section">
                  <p className="guide-text">
                    Complete <strong>{parseInt(cat.rule.min_credits)}-{parseInt(cat.rule.max_credits)} credits</strong> from any department.
                  </p>
                  {cat.rule.upper_division_min_pct && (
                    <p className="guide-text">
                      At least <strong>{cat.rule.upper_division_min_pct}%</strong> must be 3000+.
                    </p>
                  )}
                  <p className="guide-note">Use for minors or career-focused skills.</p>
                </div>
              )}
            </div>
          </div>
        )}

        {expanded && progress < 100 && !isRuleBased && available.length > 0 && (
          <div className="courses-list available-courses">
            <h4 className="courses-subtitle">Available</h4>
            {available.map((c, i) => (
              <div key={i} className="course-item incomplete">
                <div className="course-info-left">
                  <span className="course-code-label">{c.subject} {c.number_code}</span>
                  <span className="course-title-label">{c.title}</span>
                </div>
                <div className="course-info-right">
                  <span className="course-credits-label">{parseInt(c.credits)} cr</span>
                </div>
              </div>
            ))}
          </div>
        )}

        {expanded && progress < 100 && cat.choice_courses && cat.choice_courses.length > 0 && (
          <div className="choice-lists available-courses">
            {cat.choice_courses.map((group, i) => {
              const done = group.courses?.filter(c => c.completed) || [];
              const opts = group.courses?.filter(c => !c.completed) || [];
              
              return (
                <div key={i} className="choice-group">
                  <h4 className="courses-subtitle">{group.label}</h4>
                  
                  {done.length > 0 && (
                    <div className="completed-choices-section">
                      <p className="section-note">Completed:</p>
                      {done.map((c, j) => (
                        <div key={j} className="course-item completed">
                          <div className="course-info-left">
                            <span className="course-code-label">{c.subject} {c.number_code}</span>
                            <span className="course-title-label">{c.title}</span>
                          </div>
                          <div className="course-info-right">
                            <span className="course-credits-label">{parseInt(c.credits)} cr</span>
                            <span className="status-badge completed">Done</span>
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                  
                  {opts.length > 0 && (
                    <div className="available-choices-section">
                      <p className="section-note">Options:</p>
                      {opts.map((c, j) => (
                        <div key={j} className="course-item incomplete">
                          <div className="course-info-left">
                            <span className="course-code-label">{c.subject} {c.number_code}</span>
                            <span className="course-title-label">{c.title}</span>
                          </div>
                          <div className="course-info-right">
                            <span className="course-credits-label">{parseInt(c.credits)} cr</span>
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                  
                  {group.subjects && group.subjects.length > 0 && (
                    <p className="allowed-subjects">Allowed: {group.subjects.join(', ')}</p>
                  )}
                </div>
              );
            })}
          </div>
        )}
      </div>
    );
  };

  if (loading) {
    return (
      <div className="progress-container">
        <div className="progress-content">
          <div className="loading-message">Loading...</div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="progress-container">
        <div className="progress-content">
          <div className="error-message">Error: {error}</div>
          <button className="btn btn-secondary" onClick={() => navigate('/upload')}>
            Back
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="progress-container">
      <div className="progress-content">
        <div className="progress-header">
          <div className="progress-steps">
            <span className="step completed">Upload CSV</span>
            <span className="step-divider"></span>
            <span className="step completed">Analyze</span>
            <span className="step-divider"></span>
            <span className="step active">Result</span>
          </div>
          <h1 className="progress-title">Degree Progress</h1>
          <p className="progress-subtitle">Based on uploaded courses</p>
          <p className="progress-major">Major: <strong>{major}</strong></p>
        </div>

        <div className="stats-grid">
          <div className="stat-card">
            <h3 className="stat-label">Total Credits Completed</h3>
            <p className="stat-value">{parseInt(totalCredits)}</p>
          </div>
          <div className="stat-card">
            <h3 className="stat-label">Credits Remaining</h3>
            <p className="stat-value">{parseInt(remaining)}</p>
          </div>
          <div className="stat-card">
            <h3 className="stat-label">Completion Rate</h3>
            <div className="progress-bar-container">
              <div className="progress-bar" style={{ width: `${progress}%` }}></div>
            </div>
            <p className="stat-value">{progress}% Completed</p>
          </div>
        </div>

        {comparisonData?.categories && (
          <div className="requirements-section">
            <h2 className="section-title">Requirements by Category</h2>
            <div className="categories-grid">
              {comparisonData.categories.map((cat, idx) => renderCategory(cat, idx))}
            </div>
          </div>
        )}

        <div className="details-grid">
          <div className="completed-section">
            <h2 className="section-title">Completed Courses</h2>
            <div className="chart-container">
              <div className="circular-progress">
                <svg viewBox="0 0 200 200" className="circular-chart">
                  <circle
                    className="circle-bg"
                    cx="100"
                    cy="100"
                    r="80"
                  ></circle>
                  <circle
                    className="circle-progress"
                    cx="100"
                    cy="100"
                    r="80"
                    style={{
                      strokeDasharray: `${(progress / 100) * 502.65} 502.65`
                    }}
                  ></circle>
                </svg>
                <div className="chart-label">
                  <div className="chart-percent">{progress}%</div>
                  <div className="chart-text">Completed</div>
                </div>
              </div>
              <div className="chart-legend">
                <div className="legend-item">
                  <span className="legend-color completed-color"></span>
                  <span className="legend-label">Completed</span>
                </div>
                <div className="legend-item">
                  <span className="legend-color remaining-color"></span>
                  <span className="legend-label">Not Completed</span>
                </div>
              </div>
            </div>

            <div className="courses-table-wrapper">
              <h3 className="table-title">Course Name | Credits | Semester</h3>
              <div className="courses-table">
                {courses.map((course, index) => (
                  <div key={index} className="course-row">
                    <div className="course-info">
                      <span className="course-code">{course['Course Code']}</span>
                      <span className="course-name">{course['Course Name']}</span>
                    </div>
                    <div className="course-details">
                      <span className="course-credits">{parseInt(course['Credits'])}</span>
                      <span className="course-semester">{course['Semester']}</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>

        <div className="action-buttons">
          <button className="btn btn-secondary" onClick={() => navigate('/upload')}>
            Upload Again
          </button>
          <button className="btn btn-primary" onClick={goBack}>
            Home
          </button>
        </div>
      </div>
    </div>
  );
}

export default DegreeProgress;

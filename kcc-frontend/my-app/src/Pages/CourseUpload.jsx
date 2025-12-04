import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import '../styles/CourseUpload.css';

function CourseUpload() {
  const navigate = useNavigate();
  const [selectedMajor, setSelectedMajor] = useState('');
  const [majorError, setMajorError] = useState(false);
  const [file, setFile] = useState(null);
  const [dragActive, setDragActive] = useState(false);
  const [validationResult, setValidationResult] = useState(null);
  const [csvData, setCsvData] = useState([]);
  const [errors, setErrors] = useState([]);

  const majors = ['Computer Science', 'IT'];

  const validGrades = ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'F'];

  const onMajorChange = (e) => {
    setSelectedMajor(e.target.value);
    if (e.target.value) setMajorError(false);
  };

  const handleDrag = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.type === 'dragenter' || e.type === 'dragover') {
      setDragActive(true);
    } else if (e.type === 'dragleave') {
      setDragActive(false);
    }
  };

  const handleDrop = (e) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);

    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      handleFileSelect(e.dataTransfer.files[0]);
    }
  };

  const handleFileInput = (e) => {
    if (e.target.files && e.target.files[0]) {
      handleFileSelect(e.target.files[0]);
    }
  };

  const checkFile = (file) => {
    const maxSize = 5 * 1024 * 1024;
    return {
      validType: file.name.toLowerCase().endsWith('.csv'),
      validSize: file.size <= maxSize
    };
  };

  const parseCSV = (text) => {
    const lines = text.split('\n').filter(line => line.trim());
    if (lines.length === 0) {
      return { headers: [], rows: [] };
    }

    const headers = lines[0].split(',').map(h => h.trim());
    const rows = [];

    for (let i = 1; i < lines.length; i++) {
      const values = lines[i].split(',').map(v => v.trim());
      if (values.length === headers.length) {
        const row = {};
        headers.forEach((header, index) => {
          row[header] = values[index];
        });
        rows.push(row);
      }
    }

    return { headers, rows };
  };

  const validateSchema = (headers) => {
    const required = ['Course Code', 'Course Name', 'Credits', 'Grade', 'Semester'];
    const normalized = headers.map(h => h.toLowerCase().trim());

    for (let col of required) {
      if (!normalized.includes(col.toLowerCase())) {
        return { valid: false, message: `Missing column: ${col}` };
      }
    }
    return { valid: true };
  };

  const validateData = (rows) => {
    const errors = [];
    rows.forEach((row, i) => {
      const n = i + 2;
      if (!row['Course Code']?.trim()) errors.push(`Row ${n}: Missing course code`);
      if (!row['Course Name']?.trim()) errors.push(`Row ${n}: Missing course name`);
      
      const credits = parseFloat(row['Credits']);
      if (!row['Credits']?.trim()) {
        errors.push(`Row ${n}: Missing credits`);
      } else if (isNaN(credits) || credits < 0 || credits > 6) {
        errors.push(`Row ${n}: Invalid credits (must be 0-6)`);
      }

      if (!row['Grade']?.trim()) {
        errors.push(`Row ${n}: Missing grade`);
      } else if (!validGrades.includes(row['Grade'].toUpperCase())) {
        errors.push(`Row ${n}: Invalid grade "${row['Grade']}"`);
      }

      if (!row['Semester']?.trim()) errors.push(`Row ${n}: Missing semester`);
    });
    return errors;
  };

  const handleFileSelect = async (f) => {
    setErrors([]);
    setValidationResult(null);
    setCsvData([]);

    if (!selectedMajor) {
      setMajorError(true);
      setValidationResult({ success: false, message: 'Select a major first' });
      return;
    }

    const check = checkFile(f);
    if (!check.validType) {
      setValidationResult({ success: false, message: 'Invalid file type. Use CSV' });
      return;
    }

    if (!check.validSize) {
      setValidationResult({ success: false, message: 'File too large (max 5MB)' });
      return;
    }

    setFile(f);

    const reader = new FileReader();
    reader.onload = (e) => {
      try {
        const { headers, rows } = parseCSV(e.target.result);

        const schema = validateSchema(headers);
        if (!schema.valid) {
          setValidationResult({ success: false, message: schema.message });
          return;
        }

        const errs = validateData(rows);
        if (errs.length > 0) {
          setErrors(errs);
          setValidationResult({ success: false, message: `${errs.length} error(s) found` });
          return;
        }

        setCsvData(rows);
        setValidationResult({ success: true, message: `${rows.length} courses loaded` });

      } catch (error) {
        setValidationResult({ success: false, message: 'CSV read error' });
      }
    };

    reader.onerror = () => setValidationResult({ success: false, message: 'CSV read error' });
    reader.readAsText(f, 'UTF-8');
  };

  const resetForm = () => {
    setFile(null);
    setValidationResult(null);
    setCsvData([]);
    setErrors([]);
    setSelectedMajor('');
    setMajorError(false);
  };

  const viewResult = () => {
    navigate('/result', {
      state: { courses: csvData, major: selectedMajor }
    });
  };

  return (
    <div className="upload-container">
      <div className="upload-content">
        <Link to="/" className="back-home-btn">Back</Link>
        <h1 className="upload-title">Course Credit Checker</h1>
        <p className="upload-subtitle">Upload your courses to check progress</p>

        <div className="csv-info-card">
          <h3 className="csv-info-title">CSV Format</h3>
          <div className="csv-requirements">
            <div className="csv-requirement">
              <strong>Columns:</strong>
              <span>Course Code, Course Name, Credits, Grade, Semester</span>
            </div>
            <div className="csv-requirement">
              <strong>Grades:</strong>
              <span>A, A-, B+, B, B-, C+, C, C-, D, F</span>
            </div>
            <div className="csv-requirement">
              <strong>Credits:</strong>
              <span>0-6</span>
            </div>
          </div>
          <div className="csv-example">
            <strong>Example:</strong>
            <code>
              Course Code,Course Name,Credits,Grade,Semester<br/>
              CPS 1231,Computer Programming I,4,A,Fall 2023
            </code>
          </div>
          <a href="/sample-courses.csv" download className="download-sample">
            Download Sample
          </a>
        </div>

        <div className="major-selection">
          <label htmlFor="major-select" className="major-label">
            Select Your Major <span className="required">*</span>
          </label>
          <select
            id="major-select"
            value={selectedMajor}
            onChange={onMajorChange}
            className={`major-dropdown ${majorError ? 'error' : ''}`}
          >
            <option value="">-- Select a Major --</option>
            {majors.map((major) => (
              <option key={major} value={major}>
                {major}
              </option>
            ))}
          </select>
          {majorError && (
            <p className="error-message">Please select a major before uploading</p>
          )}
        </div>

        <div
          className={`upload-zone ${dragActive ? 'drag-active' : ''}`}
          onDragEnter={handleDrag}
          onDragLeave={handleDrag}
          onDragOver={handleDrag}
          onDrop={handleDrop}
        >
          <input
            type="file"
            id="file-input"
            accept=".csv"
            onChange={handleFileInput}
            className="file-input"
          />
          <label htmlFor="file-input" className="upload-label">
            <div className="upload-icon">📄</div>
            <p className="upload-text">
              {file ? file.name : 'Drop CSV here or click to browse'}
            </p>
            <p className="upload-hint">Max 5MB</p>
          </label>
        </div>

        {validationResult && (
          <div className={`validation-result ${validationResult.success ? 'success' : 'error'}`}>
            <p className="result-message">{validationResult.message}</p>
          </div>
        )}

        {errors.length > 0 && (
          <div className="error-details">
            <h3>Validation Errors:</h3>
            <ul>
              {errors.map((error, index) => (
                <li key={index}>{error}</li>
              ))}
            </ul>
          </div>
        )}

        {csvData.length > 0 && (
          <div className="data-preview">
            <h3>Uploaded Courses ({csvData.length})</h3>
            <div className="table-wrapper">
              <table className="data-table">
                <thead>
                  <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Credits</th>
                    <th>Grade</th>
                    <th>Semester</th>
                  </tr>
                </thead>
                <tbody>
                  {csvData.map((row, index) => (
                    <tr key={index}>
                      <td>{row['Course Code']}</td>
                      <td>{row['Course Name']}</td>
                      <td>{row['Credits']}</td>
                      <td>{row['Grade']}</td>
                      <td>{row['Semester']}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {validationResult?.success && csvData.length > 0 && (
          <div className="action-buttons-group">
            <button className="view-result-button" onClick={viewResult}>
              View Result
            </button>
            <button className="reset-button" onClick={resetForm}>
              Try Another
            </button>
          </div>
        )}

        {validationResult && !validationResult.success && (
          <button className="reset-button" onClick={resetForm}>
            Try Again
          </button>
        )}
      </div>
    </div>
  );
}

export default CourseUpload;
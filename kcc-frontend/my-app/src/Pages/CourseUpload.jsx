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
    const requiredHeaders = ['Course Code', 'Course Name', 'Credits', 'Grade', 'Semester'];
    const normalizedHeaders = headers.map(h => h.toLowerCase().trim());

    for (let required of requiredHeaders) {
      if (!normalizedHeaders.includes(required.toLowerCase())) {
        return {
          valid: false,
          message: `Missing required header: ${required}`
        };
      }
    }

    return { valid: true };
  };

  const validateData = (rows) => {
    const errorList = [];

    rows.forEach((row, index) => {
      const rowNumber = index + 2;

      if (!row['Course Code'] || row['Course Code'].trim() === '') {
        errorList.push(`Row ${rowNumber}: Course Code is empty`);
      }

      if (!row['Course Name'] || row['Course Name'].trim() === '') {
        errorList.push(`Row ${rowNumber}: Course Name is empty`);
      }

      if (!row['Credits'] || row['Credits'].trim() === '') {
        errorList.push(`Row ${rowNumber}: Credits is empty`);
      } else {
        const credits = parseFloat(row['Credits']);
        if (isNaN(credits) || credits < 0 || credits > 6) {
          errorList.push(`Row ${rowNumber}: Credits must be a number between 0 and 6`);
        }
      }

      if (!row['Grade'] || row['Grade'].trim() === '') {
        errorList.push(`Row ${rowNumber}: Grade is empty`);
      } else {
        if (!validGrades.includes(row['Grade'].toUpperCase())) {
          errorList.push(`Row ${rowNumber}: Grade "${row['Grade']}" is not valid. Must be one of: ${validGrades.join(', ')}`);
        }
      }

      if (!row['Semester'] || row['Semester'].trim() === '') {
        errorList.push(`Row ${rowNumber}: Semester is empty`);
      }
    });

    return errorList;
  };

  const handleFileSelect = async (selectedFile) => {
    setErrors([]);
    setValidationResult(null);
    setCsvData([]);

    if (!selectedMajor) {
      setMajorError(true);
      setValidationResult({
        success: false,
        message: 'Please select a major before uploading a file.'
      });
      return;
    }

    const fileCheck = checkFile(selectedFile);
    if (!fileCheck.validType) {
      setValidationResult({
        success: false,
        message: 'Invalid file type. Please upload a CSV file.'
      });
      return;
    }

    if (!fileCheck.validSize) {
      setValidationResult({
        success: false,
        message: 'File size exceeds 5MB limit.'
      });
      return;
    }

    setFile(selectedFile);

    const reader = new FileReader();
    reader.onload = (e) => {
      try {
        const text = e.target.result;
        
        const { headers, rows } = parseCSV(text);

        const schemaValidation = validateSchema(headers);
        if (!schemaValidation.valid) {
          setValidationResult({
            success: false,
            message: schemaValidation.message
          });
          return;
        }

        const dataErrors = validateData(rows);
        if (dataErrors.length > 0) {
          setErrors(dataErrors);
          setValidationResult({
            success: false,
            message: `Found ${dataErrors.length} validation error(s) in the CSV file.`
          });
          return;
        }

        setCsvData(rows);
        setValidationResult({
          success: true,
          message: `File validated successfully! Found ${rows.length} course(s).`
        });

      } catch (error) {
        setValidationResult({
          success: false,
          message: 'Error reading file. Please ensure it is a valid UTF-8 encoded CSV file.'
        });
      }
    };

    reader.onerror = () => {
      setValidationResult({
        success: false,
        message: 'Error reading file. Please ensure it is a valid UTF-8 encoded CSV file.'
      });
    };

    reader.readAsText(selectedFile, 'UTF-8');
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
        <Link to="/" className="back-home-btn">Back to Home</Link>
        <h1 className="upload-title">Course Credit Checker</h1>
        <p className="upload-subtitle">Upload your completed courses to check your progress</p>

        <div className="csv-info-card">
          <h3 className="csv-info-title">CSV File Format Requirements</h3>
          <div className="csv-requirements">
            <div className="csv-requirement">
              <strong>Required Columns:</strong>
              <span>Course Code, Course Name, Credits, Grade, Semester</span>
            </div>
            <div className="csv-requirement">
              <strong>Valid Grades:</strong>
              <span>A, A-, B+, B, B-, C+, C, C-, D, F</span>
            </div>
            <div className="csv-requirement">
              <strong>Credits Range:</strong>
              <span>0 - 6</span>
            </div>
            <div className="csv-requirement">
              <strong>File Size Limit:</strong>
              <span>Maximum 5MB</span>
            </div>
          </div>
          <div className="csv-example">
            <strong>Example Format:</strong>
            <code>
              Course Code,Course Name,Credits,Grade,Semester<br/>
              CPS 1231,Computer Programming I,4,A,Fall 2023
            </code>
          </div>
          <a href="/sample-courses.csv" download className="download-sample">
            Download Sample CSV
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
              {file ? file.name : 'Drag and drop your CSV file here'}
            </p>
            <p className="upload-subtext">or click to browse</p>
            <p className="upload-hint">Maximum file size: 5MB</p>
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

        {validationResult && validationResult.success && csvData.length > 0 && (
          <div className="action-buttons-group">
            <button className="view-result-button" onClick={viewResult}>
              View Result
            </button>
            <button className="reset-button" onClick={resetForm}>
              Upload Another File
            </button>
          </div>
        )}

        {validationResult && !validationResult.success && (
          <button className="reset-button" onClick={resetForm}>
            Upload Another File
          </button>
        )}
      </div>
    </div>
  );
}

export default CourseUpload;


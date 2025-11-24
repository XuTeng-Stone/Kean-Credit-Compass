# Kean Credit Compass - User Manual

## Introduction

Kean Credit Compass is a web-based tool designed to help Kean University Computer Science and IT students track their academic progress toward graduation. The application allows students to upload their completed courses and automatically calculates their progress against program requirements.

## Getting Started

1. Open the application in your web browser
2. Click "Start Checking" on the landing page
3. Select your major and upload your course CSV file
4. View your degree progress and completion status

## Features Overview

### 1. Landing Page

The home page provides a welcoming interface with:
- Application logo and title
- "Start Checking" button to begin the process

### 2. Course Upload

This page allows you to upload your completed courses and validate the data.

**Key Features:**
- Major selection (Computer Science or IT)
- Drag-and-drop CSV file upload
- Real-time file validation
- Data preview table
- Error detection and reporting

**File Requirements:**
- File type: CSV only
- Maximum size: 5MB
- Encoding: UTF-8

### 3. Degree Progress

After successful upload, this page displays your academic progress.

**Information Displayed:**
- Total credits completed
- Credits remaining (out of 120 required)
- Completion percentage with visual progress bar
- Circular progress chart
- Complete list of uploaded courses with details

## CSV File Requirements

### Required Columns

Your CSV file must include the following columns in the header row:
- Course Code
- Course Name
- Credits
- Grade
- Semester

### Data Specifications

**Course Code:** Any valid course code (e.g., CPS 1231)

**Course Name:** Full name of the course

**Credits:** Numeric value between 0 and 6

**Grade:** Must be one of the following valid grades:
- A, A-, B+, B, B-, C+, C, C-, D, F

**Semester:** Semester when the course was taken (e.g., Fall 2023)

### Example CSV Format

```
Course Code,Course Name,Credits,Grade,Semester
CPS 1231,Computer Programming I,4,A,Fall 2023
CPS 1232,Computer Programming II,4,B+,Spring 2024
MATH 1203,Calculus I,3,B,Fall 2023
```

### Sample File

A sample CSV file is available for download on the Course Upload page. Use it as a template for formatting your own data.

## User Workflow

### Step 1: Start the Application
- Navigate to the landing page
- Click "Start Checking"

### Step 2: Select Your Major
- Choose either "Computer Science" or "IT" from the dropdown menu
- This selection is required before uploading

### Step 3: Upload Your CSV File
- Download the sample CSV file (optional)
- Prepare your CSV file with completed courses
- Drag and drop the file into the upload zone, or click to browse
- Wait for validation to complete

### Step 4: Review Validation Results
- If successful: Preview your uploaded courses in the table
- If errors found: Review the error messages and correct your CSV file
- Use "Upload Another File" to try again if needed

### Step 5: View Your Progress
- Click "View Result" after successful validation
- Review your completion statistics
- Check completed courses and remaining credits
- Use "Re-upload CSV" to update your data
- Use "Back to Dashboard" to return to the home page

## Notes

- All fields in the CSV file are required and cannot be empty
- The application assumes 120 total credits are required for graduation
- Progress is calculated based on completed credits versus total required credits
- You can upload a new CSV file at any time to update your progress


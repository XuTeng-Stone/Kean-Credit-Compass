import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import LandingPage from './Pages/LandingPage';
import CourseUpload from './Pages/CourseUpload';
import DegreeProgress from './Pages/DegreeProgress';

function App() {
  return (
    <Router basename="/">
      <Routes>
        <Route path="/" element={<LandingPage />} />
        <Route path="/upload" element={<CourseUpload />} />
        <Route path="/result" element={<DegreeProgress />} />
      </Routes>
    </Router>
  );
}

export default App;
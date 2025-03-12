import React from 'react';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import Login from './components/Login';
import Register from './components/Register';
import GalleryComponent from './components/Gallery'; // Import GalleryComponent
import './App.css';
import API_URL from './assets/links'; // Import API_URL
import ImageUploadComponent from './components/ImageUploadComponent';

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/gallery" element={<GalleryComponent />} />
        <Route path="/upload" element={<ImageUploadComponent />} />
      </Routes>
    </Router>
  );
}

export default App;

import React from 'react';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import Login from './components/Login';
import Register from './components/Register';
import GalleryComponent from './components/Gallery'; // Import GalleryComponent
import ImageUploadComponent from './components/ImageUpload';
import Auth from './components/Auth'; // Import the Auth wrapper
import './App.css';

function App() {
  return (
    <Router>
      <Routes>
        {/* Public routes */}
        <Route path="/" element={<Login />} />
        <Route path="/register" element={<Register />} />

        {/* Protected routes */}
        <Route
          path="/gallery"
          element={
            <Auth>
              <GalleryComponent />
            </Auth>
          }
        />
        <Route
          path="/upload"
          element={
            <Auth>
              <ImageUploadComponent />
            </Auth>
          }
        />
      </Routes>
    </Router>
  );
}

export default App;
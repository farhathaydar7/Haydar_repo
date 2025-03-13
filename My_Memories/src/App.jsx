import React from 'react';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import Login from './components/Login';
import Register from './components/Register';
import GalleryComponent from './components/Gallery';
import ImageUploadComponent from './components/ImageUpload';
import Auth from './components/Auth';
import Navbar from './components/Navbar';
import './App.css';

// ProtectedLayout wraps authenticated pages with Auth and the Navbar.
const ProtectedLayout = ({ children }) => (
  <Auth>
    <Navbar />
    {children}
  </Auth>
);

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
            <ProtectedLayout>
              <GalleryComponent />
            </ProtectedLayout>
          }
        />
        <Route
          path="/upload"
          element={
            <ProtectedLayout>
              <ImageUploadComponent />
            </ProtectedLayout>
          }
        />
      </Routes>
    </Router>
  );
}

export default App;

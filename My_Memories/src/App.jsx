import React from 'react';
import { BrowserRouter as Router, Route, Routes, Outlet } from 'react-router-dom';
import Login from './components/Login';
import Register from './components/Register';
import GalleryComponent from './components/Gallery';
import ImageUploadComponent from './components/ImageUpload';
import Auth from './components/Auth';
import Navbar from './components/Navbar';
import UpdateImage from './components/UpdateImage';
import PhotoDetail from './components/PhotoDetail';

// Updated Protected Layout
const ProtectedLayout = () => (
  <Auth>
    <Navbar />
    <main style={{ marginTop: '60px' }}> {}
      <Outlet /> {}
    </main>
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
        <Route element={<ProtectedLayout />}>
          <Route path="/gallery" element={<GalleryComponent />} />
          <Route path="/upload" element={<ImageUploadComponent />} />
          <Route path="/update/:photoId" element={<UpdateImage />} />
          <Route path="/photos/:photoId" element={<PhotoDetail />} />
        </Route>

        {/* Catch-all route */}
        <Route path="*" element={<h1>404 Not Found</h1>} />
      </Routes>
    </Router>
  );
}

export default App;
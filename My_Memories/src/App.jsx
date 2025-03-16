import React from 'react';
import { BrowserRouter as Router, Routes, Route, Outlet } from 'react-router-dom';
import Login from './components/Login';
import Register from './components/Register';
import GalleryComponent from './components/Gallery';
import ImageUploadComponent from './components/ImageUpload';
import Auth from './components/Auth';
import Navbar from './components/Navbar';
import UpdateImage from './components/UpdateImage';
import PhotoDetail from './components/PhotoDetail';
import UnderDevelopment from './components/UnderDev';

const LayoutWithNavbar = () => (
  <>
    <Navbar />
    <main style={{ marginTop: '60px' }}>
      <Outlet />
    </main>
  </>
);

function App() {
  return (
    <Router>
      <Routes>
        {/* Public routes: No Navbar */}
        <Route path="/" element={<Login />} />
        <Route path="/register" element={<Register />} />

        {/* Protected routes with Navbar */}
        <Route element={<Auth><LayoutWithNavbar /></Auth>}>
          <Route path="/gallery" element={<GalleryComponent />} />
          <Route path="/upload" element={<ImageUploadComponent />} />
        </Route>

        {/* Other protected routes without Navbar */}
        <Route element={<Auth><Outlet /></Auth>}>
          <Route path="/update/:photoId" element={<UpdateImage />} />
          <Route path="/photos/:photoId" element={<PhotoDetail />} />
          <Route path="/DEV" element={<UnderDevelopment />} />
        </Route>

        <Route path="*" element={<h1>404 Not Found</h1>} />
      </Routes>
    </Router>
  );
}

export default App;

import React from 'react';
import { Link } from 'react-router-dom';
import logo from '../assets/Icon/memories_icon.png'; // Update path as needed
import LogoutButton from './Logout'; // Import the LogoutButton component
import './component.css/Nabar.css';

const Navbar = () => {
  return (
    <nav className="navbar">
      <div className="navbar-left">
        <img src={logo} alt="My Memories Logo" className="navbar-logo" />
        <span className="navbar-brand">My Memories</span>
      </div>
      <div className="navbar-right">
        <Link to="/upload" className="navbar-item">Upload</Link>
        <Link to="/gallery" className="navbar-item">Gallery</Link>
        <LogoutButton />
      </div>
    </nav>
  );
};

export default Navbar;

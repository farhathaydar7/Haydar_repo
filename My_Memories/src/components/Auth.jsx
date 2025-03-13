import React, { useEffect, useState } from 'react';
import { useNavigate, Outlet } from 'react-router-dom';
import { jwtDecode } from 'jwt-decode';

const Auth = () => {
  const navigate = useNavigate();
  const [isValid, setIsValid] = useState(false);

  useEffect(() => {
    const token = localStorage.getItem('jwt_token');
    
    if (!token) {
      navigate('/');
      return;
    }

    try {
      const decoded = jwtDecode(token);
      if (decoded.exp < Date.now() / 1000) {
        throw new Error('Token expired');
      }
      setIsValid(true);
    // eslint-disable-next-line no-unused-vars
    } catch (error) {
      localStorage.removeItem('jwt_token');
      navigate('/');
    }
  }, [navigate]);

  if (!isValid) {
    return <div style={{ padding: '20px' }}>Verifying authentication...</div>;
  }

  return <Outlet />; // This renders the nested route's element
};

export default Auth;
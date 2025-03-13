import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {jwtDecode }from 'jwt-decode';

const Auth = ({ children }) => {
  const navigate = useNavigate();
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('jwt_token');
    if (!token) {
      navigate('/');
      return;
    }

    try {
      const decodedToken = jwtDecode(token);
      const currentTime = Date.now() / 1000; // Convert to seconds
      if (decodedToken.exp < currentTime) {
        // Token is expired
        localStorage.removeItem('jwt_token');
        navigate('/');
      } else {
        setIsLoading(false); // User is authenticated
      }
    } catch (error) {
      console.error('Invalid token:', error);
      localStorage.removeItem('jwt_token');
      console.log('Please Log in');
      navigate('/');
    }
  }, [navigate]);

  if (isLoading) {
    return <div>Loading...</div>;
  }

  return <>{children}</>;
};

export default Auth;
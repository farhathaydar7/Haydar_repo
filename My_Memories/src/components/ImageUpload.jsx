import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import API from '../services/API';
import usePhoto from '../models/Photo.model';
import useTag from '../models/Tag.model';
import './component.css/Upload.css';


const Upload = () => {
  // Use the photo state and setter from the usePhoto hook
  const { photo, setPhoto, validateDescription } = usePhoto();
  const { validateName } = useTag();
  const [base64Image, setBase64Image] = useState('');
  const [mimeType, setMimeType] = useState('');
  const [userId, setUserId] = useState(null); // Initialize userId as null
  const [galleryImages, setGalleryImages] = useState([]);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  // Get user ID from local storage on component mount
  useEffect(() => {
    const userString = localStorage.getItem('user');
    if (userString) {
      try {
        const user = JSON.parse(userString);
        setUserId(user.id); // Set the user ID from local storage
      } catch (error) {
        console.error('Failed to parse user data:', error);
        navigate('/'); // Redirect to login if user data is invalid
      }
    } else {
      navigate('/'); // Redirect to login if no user data is found
    }
  }, [navigate]);

  const handleSubmit = async (e) => {
    e.preventDefault(); // Prevent default form submission behavior
  
    try {
      if (!userId) {
        throw new Error('User ID not available. Please log in again.');
      }
  
      if (!validateDescription(photo.description)) {
        throw new Error('Description must be under 500 characters');
      }
  
      if (photo.tag && !validateName(photo.tag)) {
        throw new Error('Tag must be between 2-50 characters');
      }
  
      const response = await API.uploadPhoto({
        ...photo,
        image: base64Image,
        mime_type: mimeType,
        owner_id: userId,
        tag: photo.tag // Send tag name instead of ID
      });
  
      setGalleryImages([...galleryImages, response.data]);
      setPhoto({
        title: '',
        date: new Date().toISOString().split('T')[0],
        description: '',
        tag: ''
      }); // Reset form fields
      navigate('/gallery'); // Redirect to gallery after successful upload
    } catch (error) {
      setError(error.response?.data?.error || error.message);
    }
  };
  

  const updateField = (field, value) => {
    setPhoto(prev => ({ ...prev, [field]: value || '' })); // Ensure value is never null
  };

  const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        const base64Image = reader.result; // e.g., "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQ..."
        setBase64Image(base64Image);
        setMimeType(file.type);
      };
      reader.readAsDataURL(file);
    }
  };

  return (
    <div className="upload-container">
      <h2>Upload Image</h2>
      {error && <p className="error">{error}</p>}
      <form onSubmit={handleSubmit} className="upload-form">
        <input 
          type="file" 
          accept="image/*" 
          onChange={handleImageUpload} 
          required 
        />
        <input
          type="text"
          placeholder="Title"
          value={photo.title || ''}
          onChange={(e) => updateField('title', e.target.value)}
        />
        <input
          type="date"
          value={photo.date || ''}
          onChange={(e) => updateField('date', e.target.value)}
        />
        <textarea
          placeholder="Description (max 500 characters)"
          value={photo.description || ''}
          onChange={(e) => updateField('description', e.target.value)}
        />
       <input
  type="text"
  placeholder="Tag"
  value={photo.tag || ''}
  onChange={(e) => updateField('tag', e.target.value)}
/>
        <button type="submit">Upload</button>
      </form>
    </div>
  );
};

export default Upload;
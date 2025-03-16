import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import API from '../services/API';
import usePhoto from '../models/Photo.model';
import useTag from '../models/Tag.model';
import './component.css/Upload.css';

const Upload = () => {
  const { photo, setPhoto, validateDescription } = usePhoto();
  const { validateName } = useTag();
  const [base64Image, setBase64Image] = useState('');
  const [mimeType, setMimeType] = useState('');
  const [userId] = useState('user-id-placeholder'); // Replace with actual user ID logic
  const [galleryImages, setGalleryImages] = useState([]); // Or fetch from context/props
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleSubmit = async () => {
    try {
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
        owner_id: userId
      });

      setGalleryImages([...galleryImages, response.data]);
      setPhoto({
        title: '',
        date: new Date().toISOString().split('T')[0],
        description: '',
        tag: ''
      });
      navigate('/gallery'); // Redirect to gallery after successful upload
    } catch (error) {
      setError(error.response?.data?.error || error.message);
    }
  };

  const updateField = (field, value) => {
    setPhoto(prev => ({ ...prev, [field]: value }));
  };

  const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setBase64Image(reader.result);
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
        <input type="file" accept="image/*" onChange={handleImageUpload} required />
        <input
          type="text"
          placeholder="Title"
          value={photo.title}
          onChange={(e) => updateField('title', e.target.value)}
        />
        <input
          type="date"
          value={photo.date}
          onChange={(e) => updateField('date', e.target.value)}
        />
        <textarea
          placeholder="Description (max 500 characters)"
          value={photo.description}
          onChange={(e) => updateField('description', e.target.value)}
        />
        <input
          type="text"
          placeholder="Tag"
          value={photo.tag}
          onChange={(e) => updateField('tag', e.target.value)}
        />
        <button type="submit">Upload</button>
      </form>
    </div>
  );
};

export default Upload;
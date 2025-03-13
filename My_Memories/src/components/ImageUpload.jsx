import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import API_URL from '../assets/links';
import './component.css/Gallery.css';

const Upload = () => {
  const navigate = useNavigate();
  const [previewUrl, setPreviewUrl] = useState(null);
  const [base64Image, setBase64Image] = useState('');
  const [mimeType, setMimeType] = useState('');
  const [isDragging, setIsDragging] = useState(false);
  const [memoryTitle, setMemoryTitle] = useState('');
  const [memoryDate, setMemoryDate] = useState('');
  const [tags, setTags] = useState('');
  const [description, setDescription] = useState('');
  const [galleryImages, setGalleryImages] = useState([]);
  const [error, setError] = useState('');

  // Check if the user is authenticated
  useEffect(() => {
    const token = localStorage.getItem('jwt_token');
    if (!token) {
      navigate('/login'); // Redirect to login if no token is found
    }
  }, [navigate]);

  // Handle file upload logic
  const handleFile = (file) => {
    if (!file.type.startsWith('image/')) {
      alert('Please upload an image file');
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      const parts = e.target.result.split(',');
      if (parts.length < 2) {
        console.error('Unexpected file reader result format.');
        return;
      }

      const mimeType = parts[0].match(/:(.*?);/)[1];
      const base64String = parts[1];

      setPreviewUrl(e.target.result);
      setBase64Image(base64String);
      setMimeType(mimeType);
    };
    reader.readAsDataURL(file);
  };

  // File selection handler
  const onFileSelected = (event) => {
    const file = event.target.files[0];
    if (file) {
      handleFile(file);
    }
  };

  // Drag-and-drop handlers
  const onDragOver = (event) => {
    event.preventDefault();
    setIsDragging(true);
  };

  const onDragLeave = (event) => {
    event.preventDefault();
    setIsDragging(false);
  };

  const onDrop = (event) => {
    event.preventDefault();
    setIsDragging(false);
    const file = event.dataTransfer.files[0];
    if (file) {
      handleFile(file);
    }
  };

  // Remove image preview
  const removeImage = () => {
    setPreviewUrl(null);
    setBase64Image('');
    setMimeType('');
  };

  // Submit form and upload image
  const onSubmit = async () => {
    if (!base64Image) {
      alert('Please select an image to upload.');
      return;
    }

    const token = localStorage.getItem('jwt_token');
    if (!token) {
      setError('You must be logged in to upload images.');
      navigate('/login');
      return;
    }

    const payload = {
      image: base64Image,
      mime_type: mimeType,
      title: memoryTitle,
      date: memoryDate,
      tag: tags,
      description: description,
    };

    try {
      const response = await fetch(API_URL + 'v0.1/upload.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`, // Include the JWT token
        },
        body: JSON.stringify(payload),
      });

      const text = await response.text();
      let data;
      try {
        data = JSON.parse(text);
      // eslint-disable-next-line no-unused-vars
      } catch (e) {
        console.error('Invalid JSON:', text);
        data = { error: `Server returned invalid response: ${text}` };
      }

      if (!response.ok) {
        const errorMessage = data.error || data.message || response.statusText;
        console.error('Upload failed:', errorMessage);
        setError(`Error uploading image: ${errorMessage}`);
      } else {
        alert(`Image uploaded successfully! File path: ${data.filePath}`);
        setGalleryImages([...galleryImages, payload]);
        setPreviewUrl(null);
        setBase64Image('');
        setMimeType('');
        setMemoryTitle('');
        setMemoryDate('');
        setTags('');
        setDescription('');
      }
    } catch (error) {
      console.error('Network or parsing error:', error);
      setError(`Error uploading image: ${error.message}`);
    }
  };

  return (
    <div className="gallery-container">
      {/* Upload Section */}
      <div className="upload-section">
        <h1>Upload Memory</h1>
        <div
          className={`drag-drop-area ${isDragging ? 'dragging' : ''}`}
          onDragOver={onDragOver}
          onDragLeave={onDragLeave}
          onDrop={onDrop}
        >
          {previewUrl ? (
            <>
              <div className="image-preview">
                <img
                  src={previewUrl}
                  alt="Preview"
                />
              </div>
              <button
                type="button"
                className="remove-btn"
                onClick={removeImage}
                aria-label="Remove image"
              >
                ×
              </button>
            </>
          ) : (
            <div className="drag-drop-content">
              <i className="bi bi-cloud-upload"></i>
              <p>Drag and drop your image here or</p>
              <label className="upload-btn">
                <input
                  type="file"
                  onChange={onFileSelected}
                  accept="image/*"
                  style={{ display: 'none' }}
                />
                Browse Files
              </label>
            </div>
          )}
        </div>
      </div>

      {/* Gallery Section (Form and Gallery Display) */}
      <div className="gallery-section">
        <h2>Memory Details</h2>
        <div className="form-inputs">
          <label htmlFor="memoryTitle">Memory Title:</label>
          <input
            type="text"
            id="memoryTitle"
            value={memoryTitle}
            onChange={(e) => setMemoryTitle(e.target.value)}
          />

          <label htmlFor="memoryDate">Memory Date:</label>
          <input
            type="date"
            id="memoryDate"
            value={memoryDate}
            onChange={(e) => setMemoryDate(e.target.value)}
          />

          <label htmlFor="tags">Tags:</label>
          <input
            type="text"
            id="tags"
            value={tags}
            onChange={(e) => setTags(e.target.value)}
          />

          <label htmlFor="description">Description:</label>
          <textarea
            id="description"
            value={description}
            onChange={(e) => setDescription(e.target.value)}
          />
        </div>

        <button onClick={onSubmit}>Upload Memory</button>

        {error && <p className="error-message">{error}</p>}

        <h2>Gallery</h2>
        <div className="gallery-grid">
          {galleryImages.map((image, index) => (
            <div key={index} className="group">
              <img
                src={`data:${image.mime_type};base64,${image.image}`}
                alt={image.title}
                className="w-full h-auto rounded-lg shadow-sm"
              />
              <div className="overlay">
                <div className="overlay-content">
                  <h3>{image.title ?? "Untitled"}</h3>
                  <p>{image.description ?? "No description"}</p>
                  <time>
                    {image.date ? new Date(image.date).toLocaleDateString() : "Unknown Date"}
                  </time>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default Upload;
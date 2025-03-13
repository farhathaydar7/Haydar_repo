import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import API_URL from '../assets/links';
import './component.css/Upload.css';

const Upload = () => {
  const navigate = useNavigate();
  const [previewUrl, setPreviewUrl] = useState(null);
  const [base64Image, setBase64Image] = useState('');
  const [mimeType, setMimeType] = useState('');
  const [isDragging, setIsDragging] = useState(false);
  const [memoryTitle, setMemoryTitle] = useState('');
  const [memoryDate, setMemoryDate] = useState(new Date().toISOString().split('T')[0]); // Default to today
  const [tags, setTags] = useState('');
  const [description, setDescription] = useState('');
  const [galleryImages, setGalleryImages] = useState([]);
  const [error, setError] = useState('');
  const [userId, setUserId] = useState(null);

  // Get user info from localStorage
  useEffect(() => {
    // Get user info from localStorage
    const userString = localStorage.getItem('user');
    if (userString) {
      try {
        const userData = JSON.parse(userString);
        setUserId(userData.id);
      } catch (error) {
        console.error('Failed to parse user data:', error);
        navigate('/'); // Redirect to login if data is invalid
      }
    } else {
      navigate('/'); // Redirect to login if no user data
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

    if (!userId) {
      setError('User ID not available. Please log in again.');
      navigate('/');
      return;
    }

    const token = localStorage.getItem('jwt_token');
    if (!token) {
      setError('You must be logged in to upload images.');
      navigate('/');
      return;
    }

    setError(''); // Clear any previous errors

    const payload = {
      image: base64Image,
      mime_type: mimeType,
      title: memoryTitle || 'Untitled',
      date: memoryDate || new Date().toISOString().split('T')[0],
      tag: tags,
      description: description,
      owner_id: userId 
    };

    try {
      const response = await fetch(`${API_URL}v0.1/upload.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
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
        setError(`Server returned invalid response: ${text}`);
        return;
      }

      if (!response.ok || (data && data.error)) {
        const errorMessage = data.error || data.message || response.statusText;
        console.error('Upload failed:', errorMessage);
        setError(`Error uploading image: ${errorMessage}`);
      } else {
        alert('Memory uploaded successfully!');
        // Add to gallery with the complete image data (including preview URL)
        setGalleryImages([...galleryImages, {
          ...payload,
          image: base64Image,
          preview: previewUrl
        }]);
        // Reset form fields
        setPreviewUrl(null);
        setBase64Image('');
        setMimeType('');
        setMemoryTitle('');
        setMemoryDate(new Date().toISOString().split('T')[0]);
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
            placeholder="Enter a title for your memory"
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
            placeholder="Enter tags separated by commas"
          />

          <label htmlFor="description">Description:</label>
          <textarea
            id="description"
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            placeholder="Add a description for your memory"
            rows="4"
          />
        </div>

        <button 
          onClick={onSubmit}
          // disabled={!base64Image || !userId}
          className="upload-button"
        >
          Upload Memory
        </button>

        {error && <p className="error-message">{error}</p>}

        <h2>Recently Uploaded</h2>
        <div className="gallery-grid">
          {galleryImages.length === 0 ? (
            <p className="no-images">Your uploaded memories will appear here</p>
          ) : (
            galleryImages.map((image, index) => (
              <div key={index} className="gallery-item">
                <img
                  src={image.preview || `data:${image.mime_type};base64,${image.image}`}
                  alt={image.title}
                  className="gallery-image"
                />
                <div className="overlay">
                  <div className="overlay-content">
                    <h3>{image.title || "Untitled"}</h3>
                    <p>{image.description || "No description"}</p>
                    <time>
                      {image.date ? new Date(image.date).toLocaleDateString() : "Unknown Date"}
                    </time>
                  </div>
                </div>
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
};

// Export wrapped with Auth component
export default function ProtectedUpload() {
  // Import Auth at the top level to ensure proper loading
  const Auth = React.lazy(() => import('./Auth'));
  
  return (
    <React.Suspense fallback={<div>Loading...</div>}>
      <Auth>
        <Upload />
      </Auth>
    </React.Suspense>
  );
}
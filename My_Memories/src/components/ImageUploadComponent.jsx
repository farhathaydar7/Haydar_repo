import React, { useState } from 'react';
import API_URL from '../assets/links';

const ImageUploadComponent = () => {
  const [previewUrl, setPreviewUrl] = useState(null);
  const [base64Image, setBase64Image] = useState('');
  const [mimeType, setMimeType] = useState('');
  const [isDragging, setIsDragging] = useState(false);
  const [memoryTitle, setMemoryTitle] = useState('');
  const [memoryDate, setMemoryDate] = useState('');
  const [tags, setTags] = useState('');
  const [description, setDescription] = useState('');

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

      // Extract MIME type and base64 data
      const mimeType = parts[0].match(/:(.*?);/)[1];
      const base64String = parts[1];

      setPreviewUrl(e.target.result);
      setBase64Image(base64String);
      setMimeType(mimeType);
    };
    reader.readAsDataURL(file);
  };

  const onFileSelected = (event) => {
    const file = event.target.files[0];
    if (file) {
      handleFile(file);
    }
  };

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

  const removeImage = () => {
    setPreviewUrl(null);
    setBase64Image('');
    setMimeType('');
  };

  const onSubmit = async () => {
    if (!base64Image) {
      alert('Please select an image to upload.');
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
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      const text = await response.text();
      console.log('Raw server response:', text);

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
        alert(`Error uploading image: ${errorMessage}`);
      } else {
        alert(`Image uploaded successfully! File path: ${data.filePath}`);
        // Reset form after successful upload
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
      alert(`Error uploading image: ${error.message}`);
    }
  };

  return (
    <div className="image-upload-container">
      <h1>Upload Memory</h1>
      <div
        className={`drag-drop-area ${isDragging ? 'dragging' : ''}`}
        onDragOver={onDragOver}
        onDragLeave={onDragLeave}
        onDrop={onDrop}
      >
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
        {previewUrl && (
          <div className="image-preview">
            <img
              src={previewUrl}
              alt="Preview"
              style={{ maxWidth: '200px', maxHeight: '200px' }}
            />
            <button
              type="button"
              className="remove-btn"
              onClick={removeImage}
              aria-label="Remove image"
            >
              ×
            </button>
          </div>
        )}
      </div>

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
    </div>
  );
};

export default ImageUploadComponent;
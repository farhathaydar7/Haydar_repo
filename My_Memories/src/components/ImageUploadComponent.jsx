import React, { useState } from 'react';
import API_URL from '../assets/links';

const ImageUploadComponent = () => {
  // ... (keep all existing state and handler declarations unchanged)

  const [previewUrl, setPreviewUrl] = useState(null);
  const [base64Image, setBase64Image] = useState('');
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
      // Log the full data URL for debugging
      console.log('Full data URL:', e.target.result);
      const parts = e.target.result.split(',');
      if (parts.length < 2) {
        console.error('Unexpected file reader result format.');
        return;
      }
      const base64String = parts[1];
      console.log('Base64 image string:', base64String);
      console.log('Base64 image string:', base64String);
      setPreviewUrl(e.target.result);
      setBase64Image(base64String);
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
  };

  const onSubmit = async () => {
    if (!base64Image) {
      alert('Please select an image to upload.');
      return;
    }

    // Logging cleanup (remove duplicates)
    console.log('Submitting image (first 50 chars):', base64Image.substring(0, 50) + '...');
    console.log('Base64Image length (bytes):', base64Image.length);

    const payload = {
      image: base64Image,
      title: memoryTitle,
      date: memoryDate,
      tag: tags,  // Changed from tag_id to tag
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
        // Handle non-JSON responses
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

  // ... (keep the return statement and JSX unchanged)
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
  {previewUrl && ( // Fixed the syntax here (replaced `:` with `&&`)
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

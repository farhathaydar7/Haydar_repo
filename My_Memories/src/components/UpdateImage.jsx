import React, { useState, useEffect } from 'react';
import { useNavigate, useParams, useLocation } from 'react-router-dom';
import { Box, Button, TextField, Typography, CircularProgress, FormControl, InputLabel, Select, MenuItem } from '@mui/material';
import API_URL from '../assets/links.jsx';

const UpdateImage = () => {
  const { photoId } = useParams();
  const navigate = useNavigate();
  const location = useLocation();
  const [loading, setLoading] = useState(!location.state?.initialData);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [preview, setPreview] = useState('');
  
  // Initialize form with location state if available
  const initialData = location.state?.initialData || {};
  const [formData, setFormData] = useState({
    title: initialData.title || '',
    date: initialData.date ? initialData.date.split('T')[0] : '',
    description: initialData.description || '',
    tag: initialData.tag || '',
    image: null
  });

  useEffect(() => {
    if (!location.state?.initialData) {
      const fetchPhotoData = async () => {
        try {
          const response = await fetch(`${API_URL}v0.1/get_photo.php?photo_id=${photoId}`, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('jwt_token')}`
            }
          });
          
          if (!response.ok) throw new Error('Failed to fetch photo');
          
          const { data } = await response.json();
          initializeForm(data);
        } catch (err) {
          setError(err.message);
        } finally {
          setLoading(false);
        }
      };
      fetchPhotoData();
    } else {
      setPreview(`${API_URL}${initialData.image_url}`);
      setLoading(false);
    }
  }, [photoId, location.state, initialData.image_url]);

  const initializeForm = (data) => {
    setFormData({
      title: data.title || '',
      date: data.date ? data.date.split('T')[0] : '',
      description: data.description || '',
      tag: data.tag_name || '',
      image: null
    });
    setPreview(`${API_URL}${data.image_url}`);
  };

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setPreview(reader.result);
      };
      reader.readAsDataURL(file);
      setFormData(prev => ({ ...prev, image: file }));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError('');

    try {
      const payload = {
        photo_id: photoId,
        title: formData.title,
        date: formData.date,
        description: formData.description,
        tag: formData.tag
      };

      if (formData.image) {
        const base64Image = await convertToBase64(formData.image);
        payload.image = base64Image.split(',')[1];
      }

      const response = await fetch(`${API_URL}v0.1/update.php`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('jwt_token')}`
        },
        body: JSON.stringify(payload)
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Update failed');
      }

      navigate(`/photos/${photoId}`, { 
        state: { refresh: true } 
      });
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  const convertToBase64 = (file) => {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = () => resolve(reader.result);
      reader.onerror = error => reject(error);
    });
  };

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" mt={4}>
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box component="form" onSubmit={handleSubmit} sx={{ maxWidth: 600, mx: 'auto', p: 3 }}>
      <Typography variant="h4" gutterBottom>Update Memory</Typography>
      
      {error && (
        <Typography color="error" paragraph>{error}</Typography>
      )}

      <input
        accept="image/*"
        style={{ display: 'none' }}
        id="image-upload"
        type="file"
        onChange={handleImageChange}
      />
      <label htmlFor="image-upload">
        <Button variant="contained" component="span" fullWidth sx={{ mb: 2 }}>
          {formData.image ? 'Change Image' : 'Update Image'}
        </Button>
      </label>
      
      {preview && (
        <Box sx={{ mb: 2, textAlign: 'center' }}>
          <img 
            src={preview} 
            alt="Preview" 
            style={{ maxWidth: '100%', maxHeight: '300px', borderRadius: '4px' }}
          />
        </Box>
      )}

      <TextField
        fullWidth
        label="Title"
        name="title"
        value={formData.title}
        onChange={handleChange}
        margin="normal"
        required
      />

      <TextField
        fullWidth
        label="Date"
        name="date"
        type="date"
        value={formData.date}
        onChange={handleChange}
        margin="normal"
        InputLabelProps={{ shrink: true }}
        required
      />

      <TextField
        fullWidth
        label="Description"
        name="description"
        value={formData.description}
        onChange={handleChange}
        margin="normal"
        multiline
        rows={4}
      />

      <FormControl fullWidth margin="normal">
        <InputLabel>Tag</InputLabel>
        <Select
          name="tag"
          value={formData.tag}
          onChange={handleChange}
          label="Tag"
        >
          <MenuItem value=""><em>None</em></MenuItem>
          <MenuItem value="nature">Nature</MenuItem>
          <MenuItem value="travel">Travel</MenuItem>
          <MenuItem value="family">Family</MenuItem>
          <MenuItem value="friends">Friends</MenuItem>
        </Select>
      </FormControl>

      <Button
        type="submit"
        variant="contained"
        color="primary"
        fullWidth
        sx={{ mt: 3 }}
        disabled={submitting}
      >
        {submitting ? <CircularProgress size={24} /> : 'Update Memory'}
      </Button>
    </Box>
  );
};

export default UpdateImage;
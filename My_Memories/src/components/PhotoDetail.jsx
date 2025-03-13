import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Box, Typography, Button, CircularProgress } from '@mui/material';
import API_URL from '../assets/links.jsx';

const PhotoDetail = () => {
  const { photoId } = useParams();
  const navigate = useNavigate();
  const [photo, setPhoto] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchPhoto = async () => {
      try {
        const response = await fetch(`${API_URL}v0.1/get_photo.php?photo_id=${photoId}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('jwt_token')}`
          }
        });

        if (!response.ok) throw new Error('Failed to fetch photo');
        
        const { data } = await response.json();
        setPhoto(data);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchPhoto();
  }, [photoId]);

  const handleEdit = () => {
    navigate(`/update/${photoId}`, {
      state: {
        initialData: {
          title: photo.title,
          date: photo.date,
          description: photo.description,
          tag: photo.tag_name,
          image_url: photo.image_url
        }
      }
    });
  };

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" mt={4}>
        <CircularProgress />
      </Box>
    );
  }

  if (error) {
    return (
      <Box p={4}>
        <Typography color="error">{error}</Typography>
      </Box>
    );
  }

  return (
    <Box sx={{ maxWidth: 800, mx: 'auto', p: 3 }}>
      <Button 
        variant="contained" 
        onClick={handleEdit}
        sx={{ mb: 3 }}
      >
        Edit Memory
      </Button>

      <Typography variant="h3" gutterBottom>
        {photo.title}
      </Typography>

      <Box sx={{ mb: 3 }}>
        <img
          src={`${API_URL}${photo.image_url}`}
          alt={photo.title}
          style={{ maxWidth: '100%', borderRadius: 8 }}
        />
      </Box>

      <Box sx={{ mb: 2 }}>
        <Typography variant="subtitle1" color="textSecondary">
          Date: {new Date(photo.date).toLocaleDateString()}
        </Typography>
        <Typography variant="subtitle1" color="textSecondary">
          Tag: {photo.tag_name || 'No tag'}
        </Typography>
      </Box>

      <Typography variant="body1" paragraph>
        {photo.description}
      </Typography>
    </Box>
  );
};

export default PhotoDetail;
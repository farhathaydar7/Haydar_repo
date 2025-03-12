import { useState } from 'react';

const usePhoto = () => {
  const [photo, setPhoto] = useState({
    image_id: null,
    owner_id: null,
    image_url: '',
    title: '',
    date: null,
    description: '',
    tag_id: null,
  });

  const validateImageUrl = (image_url) => {
    return /^https?:\/\/.+\..+$/.test(image_url);
  };

  const validateDescription = (description) => {
    return description.length <= 500;
  };

  const formatDate = () => {
    if (photo.date) {
      return photo.date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    }
    return '';
  };

  return {
    photo,
    setPhoto,
    validateImageUrl,
    validateDescription,
    formatDate
  };
};

export default usePhoto;
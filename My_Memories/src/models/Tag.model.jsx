import { useState } from 'react';

const useTag = () => {
  const [tag, setTag] = useState({
    tag_id: null,
    tag_name: '',
    tag_owner: null,
  });

  const validateName = (tagName) => {
    return tagName.length >= 2 && tagName.length <= 50;
  };

  return {
    tag,
    setTag,
    validateName,
  };
};

export default useTag;
import React, { useState } from 'react';

const GalleryImage = ({ image }) => {
  const [loadError, setLoadError] = useState(false);

  if (!image.image_data || !image.mime_type) return null;

  return (
    <div className="mb-4 break-inside-avoid relative group">
      <img
        src={`data:${image.mime_type};base64,${image.image_data}`}
        alt={image.title}
        className="w-full h-auto rounded-lg shadow-sm"
        onLoad={(e) => {
          if (e.target.naturalWidth === 0) {
            setLoadError(true);
          }
        }}
        onError={() => setLoadError(true)}
      />
      
      {loadError && (
        <div className="absolute inset-0 bg-red-50 flex items-center justify-center">
          <span className="text-red-500 text-sm">
            Failed to load image
          </span>
        </div>
      )}

      <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 rounded-lg flex items-end p-4 opacity-0 group-hover:opacity-100">
        <div className="text-white">
          <h3 className="font-semibold truncate">{image.title ?? "Untitled"}</h3>
          <p className="text-sm truncate">{image.description ?? "No description"}</p>
          <time className="text-xs opacity-75">
            {image.date ? new Date(image.date).toLocaleDateString() : "Unknown Date"}
          </time>
        </div>
      </div>
    </div>
  );
};

export default GalleryImage;
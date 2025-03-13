import React, { useState, useEffect } from 'react';
import API_URL from '../assets/links.jsx';
import GalleryImage from './GalleryImage';

const Skeleton = ({ height, className }) => (
  <div className={`bg-gray-200 animate-pulse rounded-lg ${className}`} style={{ height }} />
);

const GalleryComponent = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedTag, setSelectedTag] = useState(null);
  const [galleryData, setGalleryData] = useState({ tags: [], images: [] });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const token = localStorage.getItem('token');
        const params = new URLSearchParams({
          owner_id: 1,
          search: searchQuery,
          tag: selectedTag || '',
        });

        const response = await fetch(`${API_URL}v0.1/fill_gallery.php?${params}`, {
          headers: { 'Authorization': `Bearer ${token}` },
        });

        if (!response.ok) throw new Error('Failed to fetch data');
        const data = await response.json();

        setGalleryData({
          tags: data.tags,
          images: data.images.filter(img => img.image_data),
        });
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    const debounceTimer = setTimeout(fetchData, 300);
    return () => clearTimeout(debounceTimer);
  }, [searchQuery, selectedTag]);

  return (
    <div className="flex h-screen bg-gray-50">
      {/* Sidebar */}
      <div className="w-64 bg-white border-r border-gray-200 p-4 overflow-y-auto">
        <h2 className="text-lg font-semibold mb-4">Tags</h2>
        <div className="space-y-2">
          {loading ? (
            Array(3).fill().map((_, i) => <Skeleton key={i} height={40} className="w-full" />)
          ) : error ? (
            <div className="text-red-500">{error}</div>
          ) : (
            galleryData.tags.map(tag => (
              <div
                key={tag.tag_id}
                className={`flex justify-between items-center p-2 rounded cursor-pointer ${
                  selectedTag === tag.tag_id ? 'bg-blue-50 text-blue-600' : 'hover:bg-gray-100'
                }`}
                onClick={() => setSelectedTag(tag.tag_id === selectedTag ? null : tag.tag_id)}
              >
                <span className="truncate">{tag.tag_name}</span>
                <span className="text-sm text-gray-500">{tag.count}</span>
              </div>
            ))
          )}
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 p-8 overflow-y-auto">
        <div className="mb-8">
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h1 className="text-2xl font-bold truncate">
              {selectedTag 
                ? galleryData.tags.find(t => t.tag_id === selectedTag)?.tag_name 
                : 'All Photos'}
            </h1>
            <div className="w-full md:w-64">
              <input
                type="text"
                placeholder="Search memories..."
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
          </div>
        </div>

        <div className="columns-2 md:columns-3 lg:columns-4 gap-4">
          {loading ? (
            Array(12).fill().map((_, i) => <Skeleton key={i} className="aspect-square mb-4" />)
          ) : error ? (
            <div className="text-center py-8 text-red-500">{error}</div>
          ) : galleryData.images.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              No photos found {searchQuery ? `for "${searchQuery}"` : ''}
            </div>
          ) : (
            galleryData.images.map(image => (
              <div key={image.image_id} className="mb-4 break-inside-avoid relative group">
                <img
                  src={`data:${image.mime_type};base64,${image.image_data}`}
                  alt={image.title}
                  className="w-full h-auto rounded-lg shadow-sm"
                  onError={(e) => (e.target.style.display = 'none')}
                />
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
};

export default GalleryComponent;
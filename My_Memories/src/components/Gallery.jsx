import React, { useState } from 'react';

const GalleryComponent = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedTag, setSelectedTag] = useState('Family');
  
  // Mock data
  const tags = [
    { name: 'Family', count: 24 },
    { name: 'Tag 2', count: 15 },
    { name: 'Tag 3', count: 8 }
  ];

  const images = Array(12).fill(null); // 12 placeholder images

  return (
    <div className="flex h-screen bg-gray-50">
      {/* Sidebar */}
      <div className="w-64 bg-white border-r border-gray-200 p-4">
        <h2 className="text-lg font-semibold mb-4">Tags</h2>
        
        <div className="space-y-2">
          {tags.map(tag => (
            <div
              key={tag.name}
              className={`flex justify-between items-center p-2 rounded cursor-pointer ${
                selectedTag === tag.name ? 'bg-blue-50 text-blue-600' : 'hover:bg-gray-100'
              }`}
              onClick={() => setSelectedTag(tag.name)}
            >
              <span>{tag.name}</span>
              <span className="text-sm text-gray-500">{tag.count}</span>
            </div>
          ))}
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 p-8">
        {/* Header */}
        <div className="mb-8">
          <div className="flex justify-between items-center mb-6">
            <h1 className="text-2xl font-bold">{selectedTag}</h1>
            <div className="relative w-64">
              <input
                type="text"
                placeholder="Search"
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
          </div>
        </div>

        {/* Image Grid */}
        <div className="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          {images.map((_, index) => (
            <div key={index} className="aspect-square bg-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
              {/* Replace div below with actual image component */}
              <div className="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400">
                Image {index + 1}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default GalleryComponent;
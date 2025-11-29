<template>
  <div class="gallery-page">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">Ürün Galerisi</h1>
        <p class="page-subtitle">Tüm markalarımızın ürün görsellerini burada bulabilirsiniz</p>
      </div>

      <div v-if="loading" class="loading-container">
        <i class="pi pi-spin pi-spinner" style="font-size: 3rem; color: #22C55E;"></i>
      </div>

      <div v-else-if="galleries.length === 0" class="empty-state">
        <i class="pi pi-image" style="font-size: 4rem; color: #94A3B8;"></i>
        <p>Henüz galeri görseli bulunmamaktadır.</p>
      </div>

      <div v-else class="galleries-container">
        <div v-for="gallery in galleries" :key="gallery.brand" class="gallery-section">
          <h2 class="gallery-brand-title">{{ gallery.brand }}</h2>
          <div class="gallery-grid">
            <div 
              v-for="(image, index) in gallery.images" 
              :key="index"
              class="gallery-item"
              @click="openImageModal(image.url, gallery.brand)"
            >
              <img 
                :src="image.url" 
                :alt="image.name" 
                class="gallery-image"
                loading="lazy"
                decoding="async"
              />
              <div class="gallery-overlay">
                <i class="pi pi-search-plus"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Image Modal -->
    <div v-if="imageModalVisible" class="image-modal-overlay" @click="closeImageModal">
      <div class="image-modal-container" @click.stop>
        <button class="image-modal-close" @click="closeImageModal">
          <i class="pi pi-times"></i>
        </button>
        <div class="image-modal-header">
          <span class="image-modal-title">{{ selectedBrand }} - Resim Görüntüle</span>
        </div>
        <img :src="selectedImageUrl" alt="Gallery Image" class="modal-image" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const galleries = ref([]);
const loading = ref(true);
const imageModalVisible = ref(false);
const selectedImageUrl = ref('');
const selectedBrand = ref('');

const loadGalleries = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/public/gallery');
    if (response.data && response.data.galleries) {
      galleries.value = response.data.galleries;
    }
  } catch (error) {
    console.error('Error loading galleries:', error);
  } finally {
    loading.value = false;
  }
};

const openImageModal = (imageUrl, brand) => {
  selectedImageUrl.value = imageUrl;
  selectedBrand.value = brand;
  imageModalVisible.value = true;
  document.body.style.overflow = 'hidden';
};

const closeImageModal = () => {
  imageModalVisible.value = false;
  document.body.style.overflow = '';
};

onMounted(async () => {
  await loadGalleries();
});
</script>

<style scoped lang="scss">
.gallery-page {
  padding: 3rem 0 4rem;
  background: white;
  min-height: 60vh;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
}

.page-header {
  text-align: center;
  margin-bottom: 4rem;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1rem;
  
  @media (max-width: 768px) {
    font-size: 2rem;
  }
}

.page-subtitle {
  font-size: 1.1rem;
  color: #64748b;
  max-width: 600px;
  margin: 0 auto;
}

.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 4rem 0;
}

.empty-state {
  text-align: center;
  padding: 4rem 0;
  color: #94A3B8;
  
  p {
    margin-top: 1rem;
    font-size: 1.1rem;
  }
}

.galleries-container {
  display: flex;
  flex-direction: column;
  gap: 4rem;
}

.gallery-section {
  margin-bottom: 3rem;
}

.gallery-brand-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #22C55E;
  
  @media (max-width: 768px) {
    font-size: 1.5rem;
  }
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
  
  @media (max-width: 768px) {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
  }
}

.gallery-item {
  position: relative;
  aspect-ratio: 1;
  overflow: hidden;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  background: #f8fafc;
  
  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(34, 197, 94, 0.3);
    border-color: #22C55E;
    
    .gallery-image {
      transform: scale(1.1);
    }
    
    .gallery-overlay {
      opacity: 1;
    }
  }
}

.gallery-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.gallery-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(34, 197, 94, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
  
  i {
    font-size: 2.5rem;
    color: white;
  }
}

.image-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 2rem;
  animation: fadeIn 0.3s ease;
}

.image-modal-container {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.image-modal-close {
  position: absolute;
  top: -50px;
  right: 0;
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid rgba(255, 255, 255, 0.3);
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 10000;
  
  i {
    font-size: 1.5rem;
  }
  
  &:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
    transform: scale(1.1);
  }
}

.image-modal-header {
  position: absolute;
  top: -50px;
  left: 0;
  color: white;
  font-size: 1.1rem;
  font-weight: 600;
}

.modal-image {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  animation: zoomIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes zoomIn {
  from {
    transform: scale(0.9);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
</style>


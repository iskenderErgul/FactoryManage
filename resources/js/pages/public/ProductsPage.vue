<template>
  <div class="products-page">
    <div class="page-header">
      <div class="container">
        <h1 class="page-title">Ürünlerimiz</h1>
        <p class="page-subtitle">Kaliteli ve çevre dostu poşet çözümlerimizi keşfedin</p>
      </div>
    </div>

    <div class="container">
      <div class="products-container">
        <div class="products-grid" v-if="products.length">
          <Card 
            v-for="product in products" 
            :key="product.id" 
            class="product-card"
          >
            <template #header>
              <div class="product-image-wrapper">
                <img :src="product.image" :alt="product.name" class="product-image" />
              </div>
            </template>
            <template #title>
              <h3>{{ product.name }}</h3>
            </template>
            <template #content>
              <p class="product-description">{{ product.short_description }}</p>
            </template>
            <template #footer>
              <Button 
                label="Detayları Gör" 
                icon="pi pi-eye" 
                severity="success"
                outlined
                @click="viewProduct(product.id)"
              />
            </template>
          </Card>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Card from 'primevue/card';
import Button from 'primevue/button';
import axios from 'axios';

const router = useRouter();

const products = ref([
  {
    id: 1,
    name: 'Atlet Poşet',
    category: 'Market Poşetleri',
    short_description: 'Dayanıklı ve ekonomik atlet poşetleri. Her türlü alışveriş ihtiyacınız için ideal.',
    image: '',
    features: ['Ekonomik', 'Dayanıklı', 'Çevre Dostu'],
    isNew: false
  },
  {
    id: 2,
    name: 'Çöp Torbası',
    category: 'Atık Yönetimi',
    short_description: 'Sağlam ve geniş hacimli çöp torbaları. Farklı boyut seçenekleri mevcut.',
    image: '',
    features: ['Sağlam', 'Geniş Hacim', 'Farklı Boyutlar'],
    isNew: false
  },
  {
    id: 3,
    name: 'Sera Torbası',
    category: 'Tarım',
    short_description: 'Sera tarımı için özel olarak üretilmiş dayanıklı poşetler. Bitki yetiştirme için ideal.',
    image: '',
    features: ['Dayanıklı', 'UV Korumalı', 'Tarım Uyumlu'],
    isNew: false
  },
  {
    id: 4,
    name: 'Hastane Torbası',
    category: 'Tıbbi Atık',
    short_description: 'Hastane ve sağlık kuruluşları için özel olarak üretilmiş tıbbi atık torbaları. Hijyenik ve güvenli.',
    image: '',
    features: ['Hijyenik', 'Güvenli', 'Tıbbi Uyumlu'],
    isNew: false
  }
]);

const viewProduct = (id) => {
  router.push(`/urunler/${id}`);
};

const loadProductImages = async () => {
  // Her ürün için resimleri yükle
  for (const product of products.value) {
    try {
      const productName = encodeURIComponent(product.name);
      const response = await axios.get(`/api/public/gallery/product/${productName}`);
      
      if (response.data && response.data.brands && response.data.brands.length > 0) {
        // İlk markanın ilk resmini al
        const firstBrand = response.data.brands[0];
        if (firstBrand.images && firstBrand.images.length > 0) {
          product.image = firstBrand.images[0].url;
        }
      }
    } catch (error) {
      console.error(`Error loading image for ${product.name}:`, error);
      // Hata durumunda varsayılan resim kullan
      if (!product.image) {
        product.image = 'https://picsum.photos/600/400?random=' + product.id;
      }
    }
  }
};

onMounted(async () => {
  await loadProductImages();
});
</script>

<style scoped lang="scss">
.products-page {
  min-height: calc(100vh - 400px);
  background: white;
}

.page-header {
  background: linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
  color: white;
  padding: 4rem 0 3rem;
  margin-bottom: 0;
  
  .container {
    color: white;
  }
}

.page-title {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: white !important;
  
  @media (max-width: 768px) {
    font-size: 2rem;
  }
}

.page-subtitle {
  font-size: 1.3rem;
  opacity: 0.95;
  color: white !important;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  
  @media (max-width: 768px) {
    padding: 1rem;
  }
}

.page-header .container {
  background: transparent;
  color: white;
}

.products-container .container {
  background: white;
}

.products-container {
  padding-bottom: 4rem;
  background: white;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
  
  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
}

.product-card {
  transition: all 0.3s ease;
  background: white;
  
  :deep(.p-card-body) {
    padding: 1rem;
  }
  
  :deep(.p-card-title) {
    color: #1e293b;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
  }
  
  :deep(.p-card-content) {
    color: #64748b;
    padding: 0.75rem 0;
  }
  
  :deep(h3) {
    color: #1e293b;
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0;
  }
  
  &:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(34, 197, 94, 0.2);
  }
}

.product-image-wrapper {
  height: 250px;
  overflow: hidden;
  background: #f1f5f9;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
  
  .product-card:hover & {
    transform: scale(1.1);
  }
}

.product-description {
  color: #64748b;
  line-height: 1.6;
}

</style>


<template>
  <div class="product-detail-page" v-if="product">
    <div class="container">
      <!-- Breadcrumb -->
      <Breadcrumb :home="breadcrumbHome" :model="breadcrumbItems" class="breadcrumb-nav" />
      
      <!-- Product Main Info -->
      <div class="product-main">
        <div class="product-gallery">
          <Carousel 
            v-if="productImages.length > 0"
            :value="productImages" 
            :numVisible="1" 
            :numScroll="1" 
            :circular="true"
            :autoplayInterval="4000"
            class="product-carousel"
          >
            <template #item="slotProps">
              <div class="product-image-container">
                <img 
                  :src="slotProps.data.url" 
                  :alt="slotProps.data.name" 
                  class="product-main-image"
                  loading="lazy"
                  decoding="async"
                />
              </div>
            </template>
          </Carousel>
          <div v-else class="product-image-placeholder">
            <i class="pi pi-image" style="font-size: 4rem; color: #94A3B8;"></i>
            <p>Resim yükleniyor...</p>
          </div>
        </div>
        
        <div class="product-info">
          <div class="product-header">
            <h1 class="product-title">{{ product.name }}</h1>
            <Tag :value="product.category" severity="success" />
          </div>
          
          <p class="product-description">{{ product.description }}</p>
          
          <div class="product-highlights">
            <h3>Öne Çıkan Özellikler</h3>
            <ul>
              <li v-for="highlight in product.highlights" :key="highlight">
                <i class="pi pi-check-circle"></i>
                <span>{{ highlight }}</span>
              </li>
            </ul>
          </div>
          
          <div class="product-actions">
            <Button 
              label="WhatsApp'tan Yaz" 
              icon="pi pi-whatsapp" 
              severity="success"
              size="large"
              class="whatsapp-button"
              @click="openWhatsApp"
            />
          </div>
        </div>
      </div>
      
      <!-- Product Details - All as Cards -->
      <!-- Özellikler Section -->
      <section class="details-section specs-section">
        <div class="container">
          <h2 class="section-title">Özellikler</h2>
          <div class="specs-grid">
            <Card v-for="spec in product.specifications" :key="spec.label" class="spec-card">
              <template #content>
                <div class="spec-content">
                  <div class="spec-label">{{ spec.label }}</div>
                  <div class="spec-value">{{ spec.value }}</div>
                </div>
              </template>
            </Card>
          </div>
        </div>
      </section>
      
      <!-- Teknik Bilgiler Section -->
      <section class="details-section technical-section">
        <div class="container">
          <h2 class="section-title">Teknik Bilgiler</h2>
          <div class="technical-info-grid">
            <Card class="technical-card">
              <template #title>
                <h3>Malzeme ve Üretim</h3>
              </template>
              <template #content>
                <p>{{ product.technicalInfo.material }}</p>
              </template>
            </Card>
            
            <Card class="technical-card">
              <template #title>
                <h3>Boyutlar ve Kapasiteler</h3>
              </template>
              <template #content>
                <ul>
                  <li v-for="size in product.technicalInfo.sizes" :key="size">{{ size }}</li>
                </ul>
              </template>
            </Card>
          </div>
        </div>
      </section>
      
      <!-- Kullanım Alanları Section -->
      <section class="details-section usage-section">
        <div class="container">
          <h2 class="section-title">Kullanım Alanları</h2>
          <div class="usage-areas">
            <Card v-for="area in product.usageAreas" :key="area.title" class="usage-card">
              <template #title>
                <i :class="`pi ${area.icon}`"></i>
                {{ area.title }}
              </template>
              <template #content>
                <p>{{ area.description }}</p>
              </template>
            </Card>
          </div>
        </div>
      </section>
      
      <!-- Product Gallery by Brand -->
      <section class="product-gallery-section" v-if="productBrands.length > 0">
        <div class="container">
          <h2 class="section-title">Ürün Galerisi</h2>
          
          <div v-for="brand in productBrands" :key="brand.brand" class="brand-gallery">
          <h3 class="brand-title">{{ brand.brand.replace(/-/g, ' ') }}</h3>
          <div class="gallery-grid">
            <div 
              v-for="(image, index) in brand.images.slice(0, 8)" 
              :key="index"
              class="gallery-item"
              @click="openImageModal(image.url)"
            >
              <img 
                :src="image.url" 
                :alt="image.name" 
                class="gallery-image"
                loading="lazy"
                decoding="async"
              />
            </div>
          </div>
          <div class="gallery-actions" v-if="brand.images.length > 8">
            <Button 
              label="Tüm Galeriyi Gör" 
              icon="pi pi-images" 
              severity="success"
              outlined
              @click="$router.push('/galeri')"
            />
          </div>
        </div>
        </div>
      </section>
    </div>

    <!-- Image Modal -->
    <div v-if="imageModalVisible" class="image-modal-overlay" @click="closeImageModal">
      <div class="image-modal-container" @click.stop>
        <button class="image-modal-close" @click="closeImageModal">
          <i class="pi pi-times"></i>
        </button>
        <img :src="selectedImageUrl" alt="Gallery Image" class="modal-image" />
      </div>
    </div>
    
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Carousel from 'primevue/carousel';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Breadcrumb from 'primevue/breadcrumb';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const showQuoteDialog = ref(false);
const quoteForm = ref({
  name: '',
  email: '',
  phone: '',
  quantity: '',
  message: ''
});

const breadcrumbHome = ref({ icon: 'pi pi-home', to: '/' });
const breadcrumbItems = ref([
  { label: 'Ürünler', to: '/urunler' },
  { label: 'Ürün Detay' }
]);

// Tüm ürünlerin detay verileri
const allProducts = {
  1: {
    id: 1,
    name: 'Atlet Poşet',
    category: 'Market Poşetleri',
    description: 'Dayanıklı ve ekonomik atlet poşetlerimiz, günlük alışveriş ihtiyaçlarınız için mükemmel bir çözüm sunar. Yüksek kaliteli geri dönüştürülmüş malzemelerden üretilmiştir.',
    images: [
      { url: 'https://picsum.photos/800/600?random=40', alt: 'Atlet Poşet' },
      { url: 'https://picsum.photos/800/600?random=41', alt: 'Atlet Poşet 2' },
      { url: 'https://picsum.photos/800/600?random=42', alt: 'Atlet 3' },
      { url: 'https://picsum.photos/800/600?random=43', alt: 'Atlet 4' }
    ],
    highlights: [
      'Yüksek taşıma kapasitesi',
      '%100 geri dönüştürülebilir',
      'Ekonomik ve dayanıklı',
      'Çevre dostu üretim',
      'Farklı renk ve boyut seçenekleri',
      'Özel baskı imkanı'
    ],
    specifications: [
      { label: 'Malzeme', value: 'HDPE/LDPE' },
      { label: 'Kalınlık', value: '15-30 mikron' },
      { label: 'Minimum Sipariş', value: '10,000 adet' },
      { label: 'Üretim Süresi', value: '7-10 iş günü' },
      { label: 'Renk Seçenekleri', value: 'Tüm renkler mevcut' },
      { label: 'Baskı', value: 'Flexo/Ofset baskı' }
    ],
    technicalInfo: {
      material: 'Yüksek kaliteli HDPE ve LDPE malzemelerden üretilmektedir. Gıda ile temas edebilir sertifikasına sahiptir.',
      sizes: [
        'Küçük: 20x30 cm - 5 kg taşıma',
        'Orta: 30x40 cm - 10 kg taşıma',
        'Büyük: 40x50 cm - 15 kg taşıma',
        'Jumbo: 50x60 cm - 20 kg taşıma'
      ],
      certificates: ['ISO 9001', 'ISO 14001', 'TSE', 'Gıda Uygunluk']
    },
    usageAreas: [
      {
        icon: 'pi-shopping-cart',
        title: 'Market ve Süpermarketler',
        description: 'Günlük alışveriş için ideal çözüm. Dayanıklı yapısı sayesinde ağır yükleri taşıyabilir.'
      },
      {
        icon: 'pi-shop',
        title: 'Tekstil ve Giyim Mağazaları',
        description: 'Ürünlerinizi zarif bir şekilde paketlemek için kullanabilirsiniz.'
      },
      {
        icon: 'pi-building',
        title: 'Toptan ve Perakende',
        description: 'Hem toptan hem de perakende satış noktaları için uygun.'
      },
      {
        icon: 'pi-box',
        title: 'E-ticaret Paketleme',
        description: 'Online satışlarınızda ürün paketleme için kullanabilirsiniz.'
      }
    ]
  },
  2: {
    id: 2,
    name: 'Çöp Torbası',
    category: 'Atık Yönetimi',
    description: 'Sağlam ve geniş hacimli çöp torbaları. Farklı boyut seçenekleri mevcut. Dayanıklı yapısı sayesinde ağır atıkları güvenle taşıyabilirsiniz.',
    images: [
      { url: 'https://picsum.photos/800/600?random=50', alt: 'Çöp' }
    ],
    highlights: [
      'Sağlam yapı',
      'Geniş hacim',
      'Farklı boyutlar',
      'Sızdırmaz',
      'Çevre dostu',
      'Ekonomik'
    ],
    specifications: [
      { label: 'Malzeme', value: 'HDPE' },
      { label: 'Kalınlık', value: '20-50 mikron' },
      { label: 'Minimum Sipariş', value: '5,000 adet' },
      { label: 'Üretim Süresi', value: '5-7 iş günü' },
      { label: 'Renk Seçenekleri', value: 'Siyah, Beyaz, Şeffaf' },
      { label: 'Baskı', value: 'Opsiyonel' }
    ],
    technicalInfo: {
      material: 'Yüksek kaliteli HDPE malzemeden üretilmektedir. Dayanıklı ve sızdırmaz yapıya sahiptir.',
      sizes: [
        'Küçük: 30x40 cm - 10 litre',
        'Orta: 40x50 cm - 20 litre',
        'Büyük: 50x60 cm - 30 litre',
        'Jumbo: 60x70 cm - 50 litre'
      ],
      certificates: ['ISO 9001', 'TSE']
    },
    usageAreas: [
      {
        icon: 'pi-home',
        title: 'Ev Kullanımı',
        description: 'Günlük ev atıkları için ideal çözüm.'
      },
      {
        icon: 'pi-building',
        title: 'İş Yerleri',
        description: 'Ofis ve iş yerlerinde atık yönetimi için kullanılabilir.'
      }
    ]
  },
  3: {
    id: 3,
    name: 'Sera Torbası',
    category: 'Tarım',
    description: 'Sera tarımı için özel olarak üretilmiş dayanıklı poşetler. Bitki yetiştirme ve tarımsal uygulamalar için ideal çözüm.',
    images: [
      { url: 'https://picsum.photos/800/600?random=51', alt: 'Sera' }
    ],
    highlights: [
      'Dayanıklı yapı',
      'UV korumalı',
      'Tarım uyumlu',
      'Geniş kullanım alanı',
      'Uzun ömürlü',
      'Çevre dostu'
    ],
    specifications: [
      { label: 'Malzeme', value: 'HDPE/LDPE' },
      { label: 'Kalınlık', value: '30-60 mikron' },
      { label: 'Minimum Sipariş', value: '5,000 adet' },
      { label: 'Üretim Süresi', value: '7-10 iş günü' },
      { label: 'Renk Seçenekleri', value: 'Şeffaf, Siyah, Yeşil' },
      { label: 'Baskı', value: 'Opsiyonel' }
    ],
    technicalInfo: {
      material: 'Yüksek kaliteli HDPE ve LDPE malzemelerden üretilmektedir. UV ışınlarına dayanıklı özel katkı maddeleri içerir.',
      sizes: [
        'Küçük: 30x40 cm',
        'Orta: 40x50 cm',
        'Büyük: 50x60 cm',
        'Jumbo: 60x80 cm'
      ],
      certificates: ['ISO 9001', 'TSE', 'Tarım Uygunluk']
    },
    usageAreas: [
      {
        icon: 'pi-leaf',
        title: 'Sera Tarımı',
        description: 'Sera içi bitki yetiştirme ve tarımsal uygulamalar için ideal.'
      },
      {
        icon: 'pi-home',
        title: 'Bahçe Kullanımı',
        description: 'Bahçe ve tarım alanlarında çok amaçlı kullanım.'
      }
    ]
  },
  4: {
    id: 4,
    name: 'Hastane Torbası',
    category: 'Tıbbi Atık',
    description: 'Hastane ve sağlık kuruluşları için özel olarak üretilmiş tıbbi atık torbaları. Hijyenik ve güvenli yapısı sayesinde tıbbi atıkların güvenle taşınmasını sağlar.',
    images: [
      { url: 'https://picsum.photos/800/600?random=52', alt: 'Hastane Torbası' }
    ],
    highlights: [
      'Hijyenik yapı',
      'Tıbbi atık uyumlu',
      'Güvenli taşıma',
      'Dayanıklı',
      'Renkli kodlama',
      'CE işaretli'
    ],
    specifications: [
      { label: 'Malzeme', value: 'HDPE' },
      { label: 'Kalınlık', value: '40-80 mikron' },
      { label: 'Minimum Sipariş', value: '3,000 adet' },
      { label: 'Üretim Süresi', value: '7-10 iş günü' },
      { label: 'Renk Seçenekleri', value: 'Kırmızı, Sarı, Mavi, Siyah' },
      { label: 'Baskı', value: 'Tıbbi atık işaretli' }
    ],
    technicalInfo: {
      material: 'Yüksek kaliteli HDPE malzemeden üretilmektedir. Tıbbi atık yönetimi standartlarına uygundur.',
      sizes: [
        'Küçük: 30x40 cm - 10 litre',
        'Orta: 40x50 cm - 20 litre',
        'Büyük: 50x60 cm - 30 litre',
        'Jumbo: 60x80 cm - 50 litre'
      ],
      certificates: ['ISO 9001', 'ISO 13485', 'CE', 'Tıbbi Atık Uygunluk']
    },
    usageAreas: [
      {
        icon: 'pi-hospital',
        title: 'Hastaneler',
        description: 'Hastane ve sağlık kuruluşlarında tıbbi atık yönetimi için ideal.'
      },
      {
        icon: 'pi-heart',
        title: 'Sağlık Merkezleri',
        description: 'Klinik ve sağlık merkezlerinde güvenli atık toplama.'
      },
      {
        icon: 'pi-shield',
        title: 'Laboratuvarlar',
        description: 'Laboratuvar atıklarının güvenli taşınması için kullanılabilir.'
      }
    ]
  }
};

const product = ref(null);

const productBrands = ref([]);
const productImages = ref([]);
const imageModalVisible = ref(false);
const selectedImageUrl = ref('');

const openImageModal = (imageUrl) => {
  selectedImageUrl.value = imageUrl;
  imageModalVisible.value = true;
  document.body.style.overflow = 'hidden';
};

const closeImageModal = () => {
  imageModalVisible.value = false;
  document.body.style.overflow = '';
};

const openWhatsApp = () => {
  // WhatsApp numarası - buraya gerçek numara eklenebilir
  const phoneNumber = '905551234567'; // Örnek numara
  const productName = product.value.name || 'Ürün';
  const message = encodeURIComponent(`Merhaba, ${productName} hakkında bilgi almak istiyorum.`);
  window.open(`https://wa.me/${phoneNumber}?text=${message}`, '_blank');
};

const loadProductImages = async () => {
  try {
    if (!product.value || !product.value.name) {
      productImages.value = [];
      return;
    }
    
    // Ürün adını kullanarak resimleri çek
    const productName = encodeURIComponent(product.value.name);
    const response = await axios.get(`/api/public/gallery/product/${productName}`);
    
    if (response.data && response.data.brands) {
      productBrands.value = response.data.brands;
      
      // Her markadan sadece ilk resmi al
      const carouselImages = [];
      response.data.brands.forEach(brand => {
        if (brand.images && brand.images.length > 0) {
          carouselImages.push({
            url: brand.images[0].url,
            name: brand.images[0].name,
            brand: brand.brand
          });
        }
      });
      
      // Eğer resim yoksa, varsayılan resimleri kullan
      if (carouselImages.length === 0) {
        productImages.value = product.value.images || [];
      } else {
        productImages.value = carouselImages;
      }
    } else {
      // API'den resim gelmezse varsayılan resimleri kullan
      productImages.value = product.value.images || [];
    }
  } catch (error) {
    console.error('Error loading product images:', error);
    // Hata durumunda varsayılan resimleri kullan
    productImages.value = product.value?.images || [];
  }
};

const loadProduct = async (productId) => {
  const id = parseInt(productId);
  
  // Ürün verilerini ID'ye göre yükle
  if (allProducts[id]) {
    product.value = allProducts[id];
  } else {
    // Eğer ürün bulunamazsa varsayılan ürünü göster
    product.value = allProducts[1];
  }
  
  // Breadcrumb'ı güncelle
  breadcrumbItems.value = [
    { label: 'Ürünler', to: '/urunler' },
    { label: product.value.name }
  ];
  
  // Load product images by brand
  await loadProductImages();
};

// Route değişikliklerini izle
watch(() => route.params.id, async (newId) => {
  if (newId) {
    await loadProduct(newId);
  }
}, { immediate: false });

onMounted(async () => {
  await loadProduct(route.params.id);
});
</script>

<style scoped lang="scss">
.product-detail-page {
  padding: 2rem 0 4rem;
}

.container {
  max-width: 80%;
  width: 100%;
  margin: 0 auto;
  padding: 0 2rem;
}

.breadcrumb-nav {
  margin-bottom: 2rem;
  background: transparent;
  border: none;
  padding: 0;
}

.product-main {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  margin-bottom: 4rem;
  
  @media (max-width: 968px) {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
}

.product-gallery {
  position: relative;
  
  .product-carousel {
    :deep(.p-carousel) {
      max-width: 100%;
    }
    
    :deep(.p-carousel-content) {
      position: relative;
    }
    
    // Navigation arrows inside
    :deep(.p-carousel-prev),
    :deep(.p-carousel-next) {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 10;
      background: rgba(22, 163, 74, 0.8);
      color: white;
      width: 3rem;
      height: 3rem;
      border-radius: 50%;
      border: none;
      transition: all 0.3s ease;
      
      &:hover {
        background: rgba(22, 163, 74, 1);
        transform: translateY(-50%) scale(1.1);
      }
      
      @media (max-width: 768px) {
        width: 2.5rem;
        height: 2.5rem;
      }
    }
    
    :deep(.p-carousel-prev) {
      left: 1rem;
    }
    
    :deep(.p-carousel-next) {
      right: 1rem;
    }
    
    // Indicators below
    :deep(.p-carousel-indicators) {
      position: absolute;
      bottom: 2rem;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10;
      margin: 0;
      padding: 0;
      
      @media (max-width: 768px) {
        bottom: 1.5rem;
      }
      
      .p-carousel-indicator {
        button {
          background: rgba(255, 255, 255, 0.5);
          width: 12px;
          height: 12px;
          border-radius: 50%;
          border: 2px solid rgba(255, 255, 255, 0.8);
          transition: all 0.3s ease;
          
          &:hover {
            background: rgba(255, 255, 255, 0.8);
            transform: scale(1.2);
          }
        }
        
        &.p-highlight button {
          background: #22C55E;
          border-color: #22C55E;
        }
      }
    }
  }
  
  .product-image-container {
    width: 100%;
    height: 500px;
    overflow: hidden;
    border-radius: 12px;
    
    @media (max-width: 768px) {
      height: 300px;
    }
  }
  
  .product-main-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
  }
  
  .product-image-placeholder {
    width: 100%;
    height: 500px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    border-radius: 12px;
    color: #94A3B8;
    
    p {
      margin-top: 1rem;
      font-size: 1.1rem;
    }
    
    @media (max-width: 768px) {
      height: 300px;
    }
  }
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.product-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.product-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1e293b;
  
  @media (max-width: 768px) {
    font-size: 2rem;
  }
}

.product-description {
  font-size: 1.1rem;
  line-height: 1.8;
  color: #475569;
}

.product-highlights {
  h3 {
    font-size: 1.3rem;
    margin-bottom: 1rem;
    color: #1e293b;
  }
  
  ul {
    list-style: none;
    padding: 0;
    
    li {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.5rem 0;
      
      i {
        color: #22C55E;
        font-size: 1.2rem;
      }
      
      span {
        color: #475569;
        font-size: 1rem;
      }
    }
  }
}

.product-actions {
  display: flex;
  gap: 1rem;
  
  @media (max-width: 768px) {
    flex-direction: column;
  }
}

.details-section {
  padding: 4rem 0;
  
  .container {
    max-width: 80%;
    width: 100%;
    margin: 0 auto;
    padding: 0 2rem;
  }
  
  .section-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid #22C55E;
    text-align: center;
    
    @media (max-width: 768px) {
      font-size: 1.5rem;
    }
  }
}

.specs-section {
  background: white;
}

.technical-section {
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.usage-section {
  background: white;
}

.specs-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}

.spec-card {
  transition: all 0.3s ease;
  border: 2px solid #E2E8F0;
  background: white !important;
  
  :deep(.p-card) {
    background: white !important;
  }
  
  :deep(.p-card-body) {
    background: white !important;
    padding: 1.5rem;
  }
  
  :deep(.p-card-content) {
    background: white !important;
    padding: 0;
  }
  
  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(34, 197, 94, 0.2);
    border-color: #22C55E;
  }
}

.spec-content {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.spec-label {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.95rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #64748b;
}

.spec-value {
  color: #1e293b;
  font-size: 1.1rem;
  font-weight: 600;
  color: #16A34A;
}

.technical-info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.technical-card {
  transition: all 0.3s ease;
  border: 2px solid #E2E8F0;
  background: white !important;
  
  :deep(.p-card) {
    background: white !important;
  }
  
  :deep(.p-card-body) {
    background: white !important;
  }
  
  :deep(.p-card-header) {
    background: white !important;
  }
  
  :deep(.p-card-content) {
    background: white !important;
  }
  
  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(34, 197, 94, 0.2);
    border-color: #22C55E;
  }
  
  :deep(.p-card-title) {
    h3 {
      font-size: 1.2rem;
      color: #1e293b;
      margin: 0;
      font-weight: 600;
    }
  }
  
  :deep(.p-card-content) {
    p {
      color: #475569;
      line-height: 1.8;
      margin: 0;
    }
    
    ul {
      color: #475569;
      line-height: 2;
      margin: 0;
      padding-left: 1.5rem;
    }
  }
}

.certificates {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  
  .cert-tag {
    font-size: 0.95rem;
  }
}

.usage-areas {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}

.usage-card {
  background: white !important;
  
  :deep(.p-card) {
    background: white !important;
  }
  
  :deep(.p-card-body) {
    background: white !important;
  }
  
  :deep(.p-card-header) {
    background: white !important;
  }
  
  :deep(.p-card-content) {
    background: white !important;
    color: #64748b;
    line-height: 1.6;
  }
  
  :deep(.p-card-title) {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #16A34A;
    
    i {
      font-size: 1.5rem;
    }
  }
}


.whatsapp-button {
  background: linear-gradient(135deg, #25D366 0%, #128C7E 100%) !important;
  border: none !important;
  font-weight: 600;
  padding: 0.875rem 2rem;
  color: white !important;
  
  :deep(.p-button-label) {
    color: white !important;
  }
  
  :deep(.p-button-icon) {
    color: white !important;
  }
  
  &:hover {
    background: linear-gradient(135deg, #128C7E 0%, #25D366 100%) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
    
    :deep(.p-button-label) {
      color: white !important;
    }
    
    :deep(.p-button-icon) {
      color: white !important;
    }
  }
}

.product-gallery-section {
  padding: 4rem 0;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  
  .container {
    max-width: 80%;
    width: 100%;
    margin: 0 auto;
    padding: 0 2rem;
  }
  
  .section-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid #22C55E;
    text-align: center;
    
    @media (max-width: 768px) {
      font-size: 1.5rem;
    }
  }
}

.brand-gallery {
  margin-bottom: 3rem;
  
  &:last-child {
    margin-bottom: 0;
  }
}

.brand-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #22C55E;
  text-transform: capitalize;
  
  @media (max-width: 768px) {
    font-size: 1.5rem;
  }
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
  
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
  
  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(34, 197, 94, 0.3);
    border-color: #22C55E;
    
    .gallery-image {
      transform: scale(1.1);
    }
  }
}

.gallery-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.gallery-actions {
  display: flex;
  justify-content: center;
  margin-top: 1.5rem;
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

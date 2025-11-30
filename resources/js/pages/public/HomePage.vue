<template>
  <div class="home-page">
    <!-- Hero Section -->
    <section class="hero-section">
      <Carousel
        :value="heroSlides"
        :numVisible="1"
        :numScroll="1"
        :circular="true"
        :autoplayInterval="5000"
        class="hero-carousel"
      >
        <template #item="slotProps">
          <div class="hero-slide">
            <video
              v-if="slotProps.data.type === 'video'"
              :src="slotProps.data.url"
              autoplay
              muted
              loop
              playsinline
              preload="none"
              class="hero-video"
              @loadeddata="onVideoLoaded"
            ></video>
            <div
              v-else
              class="hero-slide-bg"
              :data-bg="slotProps.data.url"
              :style="{ backgroundImage: slotProps.data.loaded ? `url(${slotProps.data.url})` : 'none' }"
            ></div>
            <div class="hero-overlay">
              <div class="hero-content">
                <h1 class="hero-title animate-fade-in">{{ slotProps.data.title || 'Öz Ergül Plastik' }}</h1>
                <p class="hero-subtitle animate-fade-in-delay">{{ slotProps.data.subtitle || 'Kaliteli ve çevre dostu poşet çözümleri' }}</p>
                <div class="hero-actions animate-fade-in-delay-2">
                  <Button
                    label="Ürünlerimizi Keşfedin"
                    icon="pi pi-arrow-right"
                    severity="success"
                    size="large"
                    @click="$router.push('/urunler')"
                  />
                </div>
              </div>
            </div>
          </div>
        </template>
      </Carousel>
    </section>

    <!-- WhatsApp Banner -->
    <section class="whatsapp-banner">
      <div class="container">
        <div class="whatsapp-content">
          <div class="whatsapp-icon">
            <i class="pi pi-whatsapp"></i>
          </div>
          <div class="whatsapp-text">
            <h3>WhatsApp'tan Bize Ulaşın</h3>
            <p>Hızlı ve kolay iletişim için WhatsApp üzerinden mesaj gönderin</p>
          </div>
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
    </section>

    <!-- About Quick Section -->
    <section class="about-quick">
      <div class="container">
        <Card class="about-card">
          <template #content>
            <div class="about-content">
              <div class="about-text">
                <h2 class="section-title">Çevre Dostu Üretim Lideri</h2>
                <p class="section-desc">
                  Öz Ergül Plastik olarak, geri dönüşüm sektöründe faaliyet gösteren öncü bir plastik
                  poşet üreticisiyiz. En kaliteli ve çevre dostu poşet ürünlerini üretiyor, modern
                  tesislerimiz ve uzman ekibimizle sürdürülebilir bir gelecek için çalışıyoruz.
                </p>
                <p class="section-desc">
                  Geri dönüştürülmüş hammaddelerden üretilen poşetlerimiz, hem çevre dostu hem de
                  yüksek kalite standartlarına uygundur. Geniş ürün yelpazemiz ile farklı sektörlerden
                  müşterilerimize özel çözümler sunuyoruz. T-Shirt poşetlerden endüstriyel poşetlere,
                  çöp torbalarından özel tasarım poşetlere kadar geniş bir ürün yelpazesi ile hizmet
                  vermekteyiz.
                </p>
                <p class="section-desc">
                  Kalite, güvenilirlik ve müşteri memnuniyeti ilkelerimizden ödün vermeden, sektörde
                  öncü ve yenilikçi bir anlayışla ilerlemeye devam ediyoruz. Sürdürülebilir üretim
                  anlayışımız ve çevreye olan sorumluluğumuz, iş yapış şeklimizin temel taşlarını
                  oluşturmaktadır.
                </p>
                <Button
                  label="Daha Fazla Bilgi"
                  icon="pi pi-arrow-right"
                  severity="success"
                  class="about-button"
                  @click="$router.push('/hakkimizda')"
                />
              </div>
              <div class="about-stats">
                <div class="stat-item">
                  <div class="stat-number">%100</div>
                  <div class="stat-label">Kalite Standartı</div>
                </div>
                <div class="stat-item">
                  <div class="stat-number">1000+</div>
                  <div class="stat-label">Mutlu Müşteri</div>
                </div>
                <div class="stat-item">
                  <div class="stat-number">100%</div>
                  <div class="stat-label">Geri Dönüşüm</div>
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>
    </section>

    <!-- Products Showcase -->
    <section class="products-showcase">
      <div class="container">
        <h2 class="section-title text-center">Ürünlerimiz</h2>
        <p class="section-subtitle text-center">En kaliteli ve çevre dostu ürünlerimizi keşfedin</p>

        <div class="products-grid" v-if="products.length">
          <Card
            v-for="product in featuredProducts"
            :key="product.id"
            class="product-card"
          >
            <template #header>
              <div class="product-image-wrapper">
                <img
                  :src="product.image"
                  :alt="product.name"
                  class="product-image"
                  loading="lazy"
                  decoding="async"
                />
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
                @click="$router.push(`/urunler/${product.id}`)"
              />
            </template>
          </Card>
        </div>

        <div class="text-center mt-4">
          <Button
            label="Tüm Ürünleri Gör"
            icon="pi pi-th-large"
            severity="success"
            size="large"
            @click="$router.push('/urunler')"
          />
        </div>
      </div>
    </section>

    <!-- Why Us Section -->
    <section class="why-us-section">
      <div class="container">
        <h2 class="section-title text-center">Neden Biz?</h2>
        <p class="section-subtitle text-center">Bizi tercih etmeniz için birçok neden var</p>

        <div class="features-grid">
          <Card v-for="feature in features" :key="feature.id" class="feature-card">
            <template #content>
              <div class="feature-icon">
                <i :class="`pi ${feature.icon}`"></i>
              </div>
              <h4 class="feature-title">{{ feature.title }}</h4>
              <p class="feature-desc">{{ feature.description }}</p>
            </template>
          </Card>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import Carousel from 'primevue/carousel';
import Card from 'primevue/card';
import Button from 'primevue/button';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const heroSlides = ref([]);

const products = ref([
  {
    id: 1,
    name: 'Atlet Poşet',
    short_description: 'Dayanıklı ve ekonomik atlet poşetleri. Her türlü alışveriş ihtiyacınız için ideal.',
    image: ''
  },
  {
    id: 2,
    name: 'Çöp Torbası',
    short_description: 'Sağlam ve geniş hacimli çöp torbaları. Farklı boyut seçenekleri mevcut.',
    image: ''
  },
  {
    id: 3,
    name: 'Sera Torbası',
    short_description: 'Sera tarımı için özel olarak üretilmiş dayanıklı poşetler. Bitki yetiştirme için ideal.',
    image: ''
  },
  {
    id: 4,
    name: 'Hastane Torbası',
    short_description: 'Hastane ve sağlık kuruluşları için özel olarak üretilmiş tıbbi atık torbaları.',
    image: ''
  }
]);

const featuredProducts = computed(() => products.value);

const features = ref([
  {
    id: 1,
    icon: 'pi-check-circle',
    title: 'Çevre Dostu',
    description: '%100 geri dönüştürülebilir malzemeler kullanıyoruz'
  },
  {
    id: 2,
    icon: 'pi-verified',
    title: 'Kalite Garantisi',
    description: 'Uluslararası standartlara uygun üretim'
  },
  {
    id: 3,
    icon: 'pi-truck',
    title: 'Hızlı Teslimat',
    description: 'Türkiye geneline hızlı ve güvenli kargo'
  },
  {
    id: 4,
    icon: 'pi-dollar',
    title: 'Uygun Fiyat',
    description: 'Rekabetçi fiyatlarla kaliteli ürünler'
  },
  {
    id: 5,
    icon: 'pi-cog',
    title: 'Özel Üretim',
    description: 'İhtiyacınıza özel tasarım ve üretim'
  },
  {
    id: 6,
    icon: 'pi-users',
    title: 'Müşteri Memnuniyeti',
    description: '7/24 müşteri desteği ve danışmanlık'
  }
]);

const openWhatsApp = () => {
  // WhatsApp numarası - buraya gerçek numara eklenebilir
  const phoneNumber = '905414314943'; // Örnek numara
  const message = encodeURIComponent('Merhaba, Öz Ergül Plastik hakkında bilgi almak istiyorum.');
  window.open(`https://wa.me/${phoneNumber}?text=${message}`, '_blank');
};

const onVideoLoaded = (event) => {
  // Video yüklendiğinde otomatik oynat
  if (event.target && event.target.readyState >= 2) {
    event.target.play().catch(() => {
      // Autoplay engellenirse sessizce devam et
    });
  }
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

const loadHomepageSlider = async () => {
  try {
    const response = await axios.get('/api/public/gallery/homepage-slider');
    if (response.data && response.data.slides && response.data.slides.length > 0) {
      // Her slide için title ve subtitle ekle
      heroSlides.value = response.data.slides.map((slide, index) => ({
        ...slide,
        id: index + 1,
        loaded: index === 0, // İlk slide'ı hemen yükle
        title: index === 0 ? 'Geri Dönüşümde Öncüyüz' : index === 1 ? 'Kaliteli Üretim' : 'Modern Üretim Tesisleri',
        subtitle: index === 0 ? 'Çevre dostu, kaliteli poşet üretimi ile sürdürülebilir geleceğe katkı sağlıyoruz'
                : index === 1 ? 'Sektördeki tecrübemizle müşterilerimize en iyi hizmeti sunuyoruz'
                : 'Son teknoloji ekipmanlarımızla yüksek kaliteli üretim yapıyoruz'
      }));

      // İlk slide'ın resmini önceden yükle
      if (heroSlides.value.length > 0 && heroSlides.value[0].type === 'image') {
        const firstSlide = heroSlides.value[0];
        const img = new Image();
        img.onload = () => {
          firstSlide.loaded = true;
        };
        img.src = firstSlide.url;
      }
    } else {
      // Eğer slider dosyaları yoksa varsayılan değerleri kullan
      heroSlides.value = [
        {
          id: 1,
          type: 'image',
          url: 'https://picsum.photos/1920/700?random=1',
          title: 'Geri Dönüşümde Öncüyüz',
          subtitle: 'Çevre dostu, kaliteli poşet üretimi ile sürdürülebilir geleceğe katkı sağlıyoruz'
        }
      ];
    }
  } catch (error) {
    console.error('Error loading homepage slider:', error);
    // Hata durumunda varsayılan değer
    heroSlides.value = [
      {
        id: 1,
        type: 'image',
        url: 'https://picsum.photos/1920/700?random=1',
        title: 'Geri Dönüşümde Öncüyüz',
        subtitle: 'Çevre dostu, kaliteli poşet üretimi ile sürdürülebilir geleceğe katkı sağlıyoruz'
      }
    ];
  }
};

// Background image lazy loading için Intersection Observer
const setupLazyBackgroundImages = () => {
  nextTick(() => {
    const bgElements = document.querySelectorAll('.hero-slide-bg[data-bg]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const bgUrl = entry.target.getAttribute('data-bg');
          if (bgUrl) {
            const img = new Image();
            img.onload = () => {
              entry.target.style.backgroundImage = `url(${bgUrl})`;
              // Slide'ı loaded olarak işaretle
              const slideIndex = Array.from(bgElements).indexOf(entry.target);
              if (slideIndex >= 0 && heroSlides.value[slideIndex]) {
                heroSlides.value[slideIndex].loaded = true;
              }
            };
            img.src = bgUrl;
          }
          observer.unobserve(entry.target);
        }
      });
    }, {
      rootMargin: '50px' // 50px önceden yükle
    });

    bgElements.forEach(el => imageObserver.observe(el));
  });
};

onMounted(async () => {
  await Promise.all([
    loadHomepageSlider(),
    loadProductImages()
  ]);
  setupLazyBackgroundImages();
});
</script>

<style scoped lang="scss">
.home-page {
  width: 100%;
}

// Hero Section
.hero-section {
  margin-top: 0;
  overflow: hidden;
}

.hero-carousel {
  position: relative;

  :deep(.p-carousel-content) {
    overflow: visible;
  }

  // Indicators inside the image at bottom
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
        border-color: white;
        width: 14px;
        height: 14px;
      }
    }
  }

  // Carousel navigation arrows inside the image
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
    left: 2rem;

    @media (max-width: 768px) {
      left: 1rem;
    }
  }

  :deep(.p-carousel-next) {
    right: 2rem;

    @media (max-width: 768px) {
      right: 1rem;
    }
  }
}

// WhatsApp Banner
.whatsapp-banner {
  background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
  padding: 2rem 0;
  margin: 0;

  .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
  }
}

.whatsapp-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 2rem;

  @media (max-width: 768px) {
    flex-direction: column;
    text-align: center;
    gap: 1.5rem;
  }
}

.whatsapp-icon {
  flex-shrink: 0;

  i {
    font-size: 3.5rem;
    color: white;

    @media (max-width: 768px) {
      font-size: 3rem;
    }
  }
}

.whatsapp-text {
  flex: 1;
  color: white;

  h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: white;

    @media (max-width: 768px) {
      font-size: 1.5rem;
    }
  }

  p {
    font-size: 1.1rem;
    opacity: 0.95;
    margin: 0;
    color: white;

    @media (max-width: 768px) {
      font-size: 1rem;
    }
  }
}

.whatsapp-button {
  flex-shrink: 0;
  background: white !important;
  color: #25D366 !important;
  border: none !important;
  font-weight: 600;
  padding: 0.875rem 2rem;

  :deep(.p-button-label) {
    color: #25D366 !important;
  }

  :deep(.p-button-icon) {
    color: #25D366 !important;
  }

  &:hover {
    background: rgba(255, 255, 255, 0.9) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  }

  @media (max-width: 768px) {
    width: 100%;
    padding: 0.875rem 1.5rem;
  }
}

.hero-slide {
  position: relative;
  height: 700px;
  overflow: hidden;

  @media (max-width: 968px) {
    height: 550px;
  }

  @media (max-width: 768px) {
    height: 450px;
  }
}

.hero-slide-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  z-index: 0;
}

.hero-video {
  position: absolute;
  top: 50%;
  left: 50%;
  min-width: 100%;
  min-height: 100%;
  width: auto;
  height: auto;
  transform: translate(-50%, -50%);
  object-fit: cover;
  z-index: 0;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(22, 163, 74, 0.6) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1;
  padding: 2rem 1rem;
  box-sizing: border-box;

  @media (max-width: 768px) {
    padding: 1.5rem 0.75rem;
  }

  @media (max-width: 480px) {
    padding: 1rem 0.5rem;
  }
}

.hero-content {
  text-align: center;
  color: white;
  max-width: 900px;
  width: 100%;
  padding: 2rem 1.5rem;
  box-sizing: border-box;

  @media (max-width: 768px) {
    padding: 1.5rem 1rem;
    max-width: 100%;
  }
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  line-height: 1.2;
  word-wrap: break-word;
  overflow-wrap: break-word;

  @media (max-width: 968px) {
    font-size: 2.5rem;
  }

  @media (max-width: 768px) {
    font-size: 1.8rem;
    margin-bottom: 0.75rem;
  }

  @media (max-width: 480px) {
    font-size: 1.5rem;
  }
}

.hero-subtitle {
  font-size: 1.5rem;
  margin-bottom: 2rem;
  opacity: 0.95;
  line-height: 1.5;
  word-wrap: break-word;
  overflow-wrap: break-word;

  @media (max-width: 968px) {
    font-size: 1.3rem;
    margin-bottom: 1.5rem;
  }

  @media (max-width: 768px) {
    font-size: 1rem;
    margin-bottom: 1.25rem;
  }

  @media (max-width: 480px) {
    font-size: 0.9rem;
    margin-bottom: 1rem;
  }
}

.hero-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
  width: 100%;

  @media (max-width: 768px) {
    gap: 0.75rem;
    flex-direction: column;
    align-items: center;
  }

  :deep(.p-button) {
    @media (max-width: 768px) {
      width: 100%;
      max-width: 280px;
      font-size: 0.9rem;
      padding: 0.75rem 1.5rem;
    }

    @media (max-width: 480px) {
      font-size: 0.85rem;
      padding: 0.65rem 1.25rem;
    }
  }
}

// Animations
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fadeIn 0.8s ease-out;
}

.animate-fade-in-delay {
  animation: fadeIn 0.8s ease-out 0.2s both;
}

.animate-fade-in-delay-2 {
  animation: fadeIn 0.8s ease-out 0.4s both;
}

// Container
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;

  @media (max-width: 768px) {
    padding: 0 1rem;
  }
}

// Section Titles
.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1rem;

  @media (max-width: 768px) {
    font-size: 2rem;
  }
}

.section-subtitle {
  font-size: 1.2rem;
  color: #64748b;
  margin-bottom: 3rem;
}

.section-desc {
  font-size: 1.1rem;
  line-height: 1.8;
  color: #475569;
  margin-bottom: 1.5rem;
}

// About Quick Section
.about-quick {
  padding: 4rem 0;
  background: white;

  // Widen the container specifically for this section
  .container {
    max-width: 1400px;
  }
}

.about-card {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  background: white;
  border-radius: 16px;
}

.about-content {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr; // Give more space to text
  gap: 4rem;
  padding: 2rem;

  @media (max-width: 968px) {
    grid-template-columns: 1fr;
    gap: 3rem;
    padding: 1rem;
  }
}

.about-button {
  @media (max-width: 768px) {
    width: 100%;
  }
}

.about-stats {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  justify-content: center;
}

.stat-item {
  text-align: center;
  padding: 1.5rem;
  background: linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
  border-radius: 12px;
  color: white;
  transition: transform 0.3s ease;

  &:hover {
    transform: scale(1.05);
  }
}

.stat-number {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 1.1rem;
  opacity: 0.95;
}

// Products Showcase
.products-showcase {
  padding: 4rem 0;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2.5rem;
  margin-bottom: 2rem;

  @media (max-width: 968px) {
    grid-template-columns: 1fr;
    gap: 2rem;
  }

  @media (max-width: 768px) {
    gap: 1.5rem;
  }
}

.product-card {
  transition: all 0.3s ease;
  background: white;

  &:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(34, 197, 94, 0.2);
  }

  :deep(.p-card-title) {
    color: #1e293b !important;
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
  }

  :deep(h3) {
    color: #1e293b !important;
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
  }
}

.product-image-wrapper {
  height: 300px;
  overflow: hidden;
  background: #f1f5f9;

  @media (max-width: 968px) {
    height: 250px;
  }
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

// Why Us Section
.why-us-section {
  padding: 4rem 0;
  background: white;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2.5rem;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
}

.feature-card {
  text-align: center;
  transition: all 0.3s ease;
  background: white;
  padding: 2rem;
  border-radius: 16px;

  &:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 28px rgba(34, 197, 94, 0.2);
  }
}

.feature-icon {
  width: 120px;
  height: 120px;
  margin: 0 auto 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
  border-radius: 50%;
  color: white;
  font-size: 3.5rem;

  i {
    font-size: 3.5rem !important;
    display: block;
    line-height: 1;
    font-weight: normal;
  }

  @media (max-width: 768px) {
    width: 100px;
    height: 100px;
    font-size: 3rem;

    i {
      font-size: 3rem !important;
    }
  }
}

.feature-title {
  font-size: 1.4rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.75rem;
}

.feature-desc {
  color: #64748b;
  line-height: 1.7;
  font-size: 1rem;
}

.text-center {
  text-align: center;
}

.mt-4 {
  margin-top: 2rem;
}
</style>

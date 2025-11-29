<template>
  <div class="about-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="container">
        <h1 class="page-title">Hakkımızda</h1>
        <p class="page-subtitle">Geri dönüşümde öncü, çevreye duyarlı üretim anlayışımız</p>
      </div>
    </div>

    <div class="container">
      <!-- Company History -->
      <section class="company-history">
        <Card class="history-card">
          <template #content>
            <div class="history-content">
              <div class="history-text">
                <h2 class="section-title">Şirket Tarihimiz</h2>
                <p>
                  Öz Ergül Plastik, geri dönüşüm sektöründe faaliyet gösteren öncü bir plastik poşet 
                  üreticisidir. Çevreye duyarlı üretim anlayışımız ve kaliteli ürünlerimizle sektörde 
                  önemli bir konum edinmiş bulunmaktayız.
                </p>
                <p>
                  Modern ve teknolojik tesislerimizde, geri dönüştürülmüş hammaddelerden üretilen 
                  poşetlerimiz, hem çevre dostu hem de yüksek kalite standartlarına uygun olarak 
                  üretilmektedir. Uzman ekibimiz ve sürekli gelişen üretim kapasitemizle, müşterilerimize 
                  en iyi hizmeti sunmayı hedefliyoruz.
                </p>
                <p>
                  İlk günden bu yana kalite, güvenilirlik ve müşteri memnuniyeti ilkelerimizden 
                  ödün vermeden, sektörde öncü ve yenilikçi bir anlayışla ilerlemeye devam ediyoruz. 
                  Sürdürülebilir üretim anlayışımız ve çevreye olan sorumluluğumuz, iş yapış şeklimizin 
                  temel taşlarını oluşturmaktadır.
                </p>
                <p>
                  Geniş ürün yelpazemiz ve esnek üretim kapasitemiz sayesinde, farklı sektörlerden 
                  müşterilerimize özel çözümler sunabiliyoruz. T-Shirt poşetlerden endüstriyel poşetlere, 
                  çöp torbalarından özel tasarım poşetlere kadar geniş bir ürün yelpazesi ile 
                  hizmet vermekteyiz.
                </p>
              </div>
            </div>
          </template>
        </Card>
      </section>

      <!-- Production Process -->
      <section class="production-process">
        <h2 class="section-title text-center">Üretim Sürecimiz</h2>
        <p class="section-subtitle text-center">Modern teknoloji ile çevre dostu üretim</p>
        
        <div class="process-grid">
          <Card v-for="step in productionSteps" :key="step.id" class="process-card">
            <template #content>
              <div class="step-number">{{ step.id }}</div>
              <div class="step-icon">
                <i :class="`pi ${step.icon}`"></i>
              </div>
              <h3>{{ step.title }}</h3>
              <p>{{ step.description }}</p>
            </template>
          </Card>
        </div>
      </section>

      <!-- Mission -->
      <section class="mission-section">
        <div class="mission-grid">
          <Card class="mission-card">
            <template #title>
              <i class="pi pi-bullseye"></i>
              Misyonumuz
            </template>
            <template #content>
              <p>
                Geri dönüştürülmüş malzemelerden en yüksek kalitede ürünler üreterek, 
                çevreye olan zararı minimize etmek ve sürdürülebilir bir gelecek inşa etmektir.
              </p>
            </template>
          </Card>
          
          <Card class="mission-card">
            <template #title>
              <i class="pi pi-eye"></i>
              Vizyonumuz
            </template>
            <template #content>
              <p>
                Türkiye'nin ve bölgenin en büyük, en teknolojik ve çevre dostu poşet üreticisi 
                olmak, sektörde standartları belirleyen öncü firma konumunu sürdürmektir.
              </p>
            </template>
          </Card>
          
          <Card class="mission-card">
            <template #title>
              <i class="pi pi-heart"></i>
              Değerlerimiz
            </template>
            <template #content>
              <ul>
                <li>Kalite ve Güvenilirlik</li>
                <li>Çevre Bilinci</li>
                <li>Müşteri Odaklılık</li>
                <li>Yenilikçilik</li>
                <li>Sürdürülebilirlik</li>
              </ul>
            </template>
          </Card>
        </div>
      </section>

      <!-- Factory Gallery -->
      <section class="factory-gallery">
        <h2 class="section-title text-center">Fabrikamız</h2>
        <p class="section-subtitle text-center">Modern tesislerimizden görüntüler</p>
        
        <Carousel 
          :value="factoryImages" 
          :numVisible="1" 
          :numScroll="1" 
          :circular="true"
          :autoplayInterval="5000"
          class="factory-carousel"
        >
          <template #item="slotProps">
            <div class="factory-slide" :style="{ backgroundImage: `url(${slotProps.data.url})` }">
            </div>
          </template>
        </Carousel>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Card from 'primevue/card';
import Carousel from 'primevue/carousel';

const productionSteps = ref([
  {
    id: 1,
    icon: 'pi-filter',
    title: 'Hammadde Seçimi',
    description: 'Kaliteli geri dönüştürülmüş plastik hammaddelerin özenle seçilmesi'
  },
  {
    id: 2,
    icon: 'pi-cog',
    title: 'Ekstrüzyon',
    description: 'Modern ekstrüzyon makineleri ile film üretimi'
  },
  {
    id: 3,
    icon: 'pi-print',
    title: 'Baskı',
    description: 'Özel tasarım ve logo baskı işlemleri'
  },
  {
    id: 4,
    icon: 'pi-scissors',
    title: 'Kesim ve Şekillendirme',
    description: 'İstenilen boyut ve şekilde kesim işlemi'
  },
  {
    id: 5,
    icon: 'pi-check-circle',
    title: 'Kalite Kontrol',
    description: 'Sıkı kalite kontrol testlerinden geçiş'
  },
  {
    id: 6,
    icon: 'pi-box',
    title: 'Paketleme',
    description: 'Güvenli paketleme ve sevkiyata hazırlık'
  }
]);

const factoryImages = ref([
  {
    url: 'https://picsum.photos/1200/600?random=30',
    alt: 'Üretim Hattı'
  },
  {
    url: 'https://picsum.photos/1200/600?random=31',
    alt: 'Kalite Kontrol'
  },
  {
    url: 'https://picsum.photos/1200/600?random=32',
    alt: 'Depolama'
  },
  {
    url: 'https://picsum.photos/1200/600?random=33',
    alt: 'Ekstrüzyon'
  }
]);
</script>

<style scoped lang="scss">
.about-page {
  padding-bottom: 4rem;
  background: white;
}

.page-header {
  background: linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
  color: white;
  padding: 4rem 0 3rem;
  margin-bottom: 0;
  
  .container {
    color: white;
    background: transparent;
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

.text-center {
  text-align: center;
}

// Company History
.company-history {
  margin-bottom: 4rem;
}

.history-card {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  background: white;
  border: none;
  
  :deep(.p-card-body) {
    padding: 2rem;
  }
  
  :deep(.p-card-content) {
    padding: 0;
  }
}

.history-content {
  display: block;
}

.history-text {
  p {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #475569;
    margin-bottom: 1.5rem;
  }
}

// Production Process
.production-process {
  margin-bottom: 4rem;
  padding: 2rem 0;
}

.process-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.process-card {
  text-align: center;
  transition: all 0.3s ease;
  position: relative;
  background: white;
  border: none;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  
  :deep(.p-card-body) {
    padding: 2rem;
  }
  
  :deep(.p-card-content) {
    padding: 0;
  }
  
  &:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(34, 197, 94, 0.2);
  }
}

.step-number {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.2rem;
}

.step-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border-radius: 50%;
  
  i {
    font-size: 2.5rem;
    color: #16A34A;
  }
}

.process-card h3 {
  font-size: 1.3rem;
  color: #1e293b;
  margin-bottom: 0.75rem;
}

.process-card p {
  color: #64748b;
  line-height: 1.6;
}

// Mission Section
.mission-section {
  margin-bottom: 4rem;
  padding: 3rem 0;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border-radius: 16px;
}

.mission-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  padding: 0 2rem;
}

.mission-card {
  transition: all 0.3s ease;
  background: white;
  border: none;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  
  :deep(.p-card-body) {
    padding: 2rem;
  }
  
  &:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(34, 197, 94, 0.2);
  }
  
  :deep(.p-card-title) {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #16A34A;
    font-size: 1.5rem;
    
    i {
      font-size: 2rem;
    }
  }
  
  :deep(.p-card-content) {
    padding: 0;
    
    p {
      color: #475569;
      line-height: 1.8;
      font-size: 1.05rem;
    }
    
    ul {
      list-style: none;
      padding: 0;
      
      li {
        padding: 0.5rem 0;
        color: #475569;
        
        &:before {
          content: '✓ ';
          color: #22C55E;
          font-weight: bold;
          margin-right: 0.5rem;
        }
      }
    }
  }
}

// Factory Gallery
.factory-gallery {
  margin-bottom: 4rem;
  padding: 2rem 0;
}

.factory-carousel {
  position: relative;
  overflow: hidden;
  border-radius: 12px;
  
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

.factory-slide {
  position: relative;
  height: 600px;
  background-size: cover;
  background-position: center;
  
  @media (max-width: 968px) {
    height: 500px;
  }
  
  @media (max-width: 768px) {
    height: 400px;
  }
}
</style>

<template>
  <header class="public-header">
    <div class="header-container">
      <router-link to="/" class="logo-link">
        <div class="logo-container">
          <img src="/Logo.png" alt="Öz Ergül Plastik" class="logo-image" />
          <span class="logo-text">Öz Ergül Plastik</span>
        </div>
      </router-link>
      
      <nav class="nav-menu">
        <router-link to="/" class="nav-link" active-class="active">
          <i class="pi pi-home"></i>
          <span>Anasayfa</span>
        </router-link>
        <router-link to="/urunler" class="nav-link" active-class="active">
          <i class="pi pi-box"></i>
          <span>Ürünler</span>
        </router-link>
        <router-link to="/hakkimizda" class="nav-link" active-class="active">
          <i class="pi pi-info-circle"></i>
          <span>Hakkımızda</span>
        </router-link>
        <router-link to="/iletisim" class="nav-link" active-class="active">
          <i class="pi pi-envelope"></i>
          <span>İletişim</span>
        </router-link>
        <router-link to="/galeri" class="nav-link" active-class="active">
          <i class="pi pi-images"></i>
          <span>Galeri</span>
        </router-link>
      </nav>
      
      <Button 
        label="WhatsApp"
        icon="pi pi-whatsapp"
        severity="success"
        class="whatsapp-header-button"
        @click="openWhatsApp"
        aria-label="WhatsApp"
      />
      
      <!-- Mobile Menu Toggle -->
      <Button 
        icon="pi pi-bars" 
        class="mobile-menu-toggle"
        @click="mobileMenuOpen = !mobileMenuOpen"
        text
      />
    </div>
    
    <!-- Mobile Menu -->
    <div v-if="mobileMenuOpen" class="mobile-menu">
      <router-link to="/" class="mobile-nav-link" @click="mobileMenuOpen = false">
        <i class="pi pi-home"></i>
        <span>Anasayfa</span>
      </router-link>
      <router-link to="/urunler" class="mobile-nav-link" @click="mobileMenuOpen = false">
        <i class="pi pi-box"></i>
        <span>Ürünler</span>
      </router-link>
      <router-link to="/hakkimizda" class="mobile-nav-link" @click="mobileMenuOpen = false">
        <i class="pi pi-info-circle"></i>
        <span>Hakkımızda</span>
      </router-link>
      <router-link to="/iletisim" class="mobile-nav-link" @click="mobileMenuOpen = false">
        <i class="pi pi-envelope"></i>
        <span>İletişim</span>
      </router-link>
      <router-link to="/galeri" class="mobile-nav-link" @click="mobileMenuOpen = false">
        <i class="pi pi-images"></i>
        <span>Galeri</span>
      </router-link>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue';
import Button from 'primevue/button';

const mobileMenuOpen = ref(false);

const openWhatsApp = () => {
  // WhatsApp numarası - buraya gerçek numara eklenebilir
  const phoneNumber = '905551234567'; // Örnek numara
  const message = encodeURIComponent('Merhaba, Öz Ergül Plastik hakkında bilgi almak istiyorum.');
  window.open(`https://wa.me/${phoneNumber}?text=${message}`, '_blank');
};
</script>

<style scoped lang="scss">
.public-header {
  position: sticky;
  top: 0;
  z-index: 1000;
  background: white;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.header-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1rem 2rem;
  display: flex;
  align-items: center;
  gap: 2rem;
  
  @media (max-width: 968px) {
    padding: 1rem;
  }
}

.logo-link {
  text-decoration: none;
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  transition: transform 0.3s ease;
  
  &:hover {
    transform: scale(1.05);
  }
}

.logo-image {
  height: 40px;
  width: auto;
  object-fit: contain;
  
  @media (max-width: 768px) {
    height: 32px;
  }
}

.logo-text {
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  
  @media (max-width: 768px) {
    font-size: 1.2rem;
  }
}

.nav-menu {
  display: flex;
  align-items: center;
  gap: 2rem;
  flex: 1;
  justify-content: center;
  margin: 0 auto;
  
  @media (max-width: 968px) {
    display: none;
  }
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  text-decoration: none;
  color: #334155;
  font-weight: 500;
  font-size: 1rem;
  border-radius: 8px;
  transition: all 0.3s ease;
  
  i {
    color: #16A34A;
    font-size: 1.1rem;
  }
  
  &:hover {
    background: #F0FDF4;
    color: #16A34A;
  }
  
  &.active {
    background: linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
    color: white;
    
    i {
      color: white;
    }
  }
}

.cta-button {
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  transition: all 0.3s ease;
  color: white !important;
  
  :deep(.p-button-label) {
    color: white !important;
  }
  
  :deep(.p-button-icon) {
    color: white !important;
  }
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
    
    :deep(.p-button-label) {
      color: white !important;
    }
    
    :deep(.p-button-icon) {
      color: white !important;
    }
  }
  
  @media (max-width: 968px) {
    display: none;
  }
}

.whatsapp-header-button {
  background: linear-gradient(135deg, #25D366 0%, #128C7E 100%) !important;
  border: none !important;
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  transition: all 0.3s ease;
  color: white !important;
  
  :deep(.p-button-label) {
    color: white !important;
  }
  
  :deep(.p-button-icon) {
    color: white !important;
  }
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
    background: linear-gradient(135deg, #128C7E 0%, #25D366 100%) !important;
    
    :deep(.p-button-label) {
      color: white !important;
    }
    
    :deep(.p-button-icon) {
      color: white !important;
    }
  }
  
  @media (max-width: 968px) {
    display: none;
  }
}

.mobile-menu-toggle {
  display: none;
  font-size: 1.5rem;
  color: #16A34A;
  
  @media (max-width: 968px) {
    display: flex;
  }
}

.mobile-menu {
  display: flex;
  flex-direction: column;
  background: white;
  border-top: 1px solid #E2E8F0;
  padding: 1rem;
  gap: 0.5rem;
  
  @media (min-width: 969px) {
    display: none;
  }
}

.mobile-nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  text-decoration: none;
  color: #334155;
  font-weight: 500;
  border-radius: 8px;
  transition: all 0.3s ease;
  
  i {
    color: #16A34A;
    font-size: 1.2rem;
  }
  
  &:hover,
  &.router-link-active {
    background: #F0FDF4;
    color: #16A34A;
  }
}
</style>

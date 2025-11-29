<template>
  <div class="contact-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="container">
        <h1 class="page-title">İletişim</h1>
        <p class="page-subtitle">Bizimle iletişime geçin, size yardımcı olmaktan mutluluk duyarız</p>
      </div>
    </div>

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

    <div class="container">
      <div class="contact-content">
        <!-- Contact Form -->
        <div class="contact-form-section">
          <Card class="form-card">
            <template #title>
              <h2>Bize Ulaşın</h2>
            </template>
            <template #content>
              <form @submit.prevent="submitForm" class="p-fluid">
                <div class="field">
                  <label for="name">Ad Soyad *</label>
                  <InputText 
                    id="name" 
                    v-model="formData.name" 
                    :class="{ 'p-invalid': submitted && !formData.name }"
                    placeholder="Adınız ve soyadınız"
                  />
                  <small class="p-error" v-if="submitted && !formData.name">Ad Soyad gereklidir.</small>
                </div>

                <div class="field">
                  <label for="email">E-posta *</label>
                  <InputText 
                    id="email" 
                    v-model="formData.email" 
                    type="email"
                    :class="{ 'p-invalid': submitted && !formData.email }"
                    placeholder="ornek@email.com"
                  />
                  <small class="p-error" v-if="submitted && !formData.email">E-posta gereklidir.</small>
                </div>

                <div class="field">
                  <label for="phone">Telefon *</label>
                  <InputText 
                    id="phone" 
                    v-model="formData.phone" 
                    :class="{ 'p-invalid': submitted && !formData.phone }"
                    placeholder="+90 (555) 123 45 67"
                  />
                  <small class="p-error" v-if="submitted && !formData.phone">Telefon gereklidir.</small>
                </div>

                <div class="field">
                  <label for="subject">Konu</label>
                  <Dropdown 
                    id="subject" 
                    v-model="formData.subject" 
                    :options="subjects" 
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Konu seçin"
                  />
                </div>

                <div class="field">
                  <label for="message">Mesajınız *</label>
                  <Textarea 
                    id="message" 
                    v-model="formData.message" 
                    rows="6"
                    :class="{ 'p-invalid': submitted && !formData.message }"
                    placeholder="Mesajınızı buraya yazın..."
                  />
                  <small class="p-error" v-if="submitted && !formData.message">Mesaj gereklidir.</small>
                </div>

                <div class="form-actions">
                  <Button 
                    type="submit" 
                    label="Gönder" 
                    icon="pi pi-send" 
                    severity="success"
                    size="large"
                    :loading="loading"
                  />
                </div>
              </form>
            </template>
          </Card>
        </div>

        <!-- Contact Info -->
        <div class="contact-info-section">
          <Card class="info-card">
            <template #title>
              <h2>İletişim Bilgileri</h2>
            </template>
            <template #content>
              <div class="info-list">
                <div class="info-item">
                  <div class="info-icon">
                    <i class="pi pi-map-marker"></i>
                  </div>
                  <div class="info-details">
                    <h4>Adres</h4>
                    <p>Organize Sanayi Bölgesi<br>1. Cadde No: 123<br>34000 İstanbul, Türkiye</p>
                  </div>
                </div>

                <div class="info-item">
                  <div class="info-icon">
                    <i class="pi pi-phone"></i>
                  </div>
                  <div class="info-details">
                    <h4>Telefon</h4>
                    <p>
                      <a href="tel:+905551234567">+90 (555) 123 45 67</a><br>
                      <a href="tel:+902121234567">+90 (212) 123 45 67</a>
                    </p>
                  </div>
                </div>

                <div class="info-item">
                  <div class="info-icon">
                    <i class="pi pi-envelope"></i>
                  </div>
                  <div class="info-details">
                    <h4>E-posta</h4>
                    <p>
                      <a href="mailto:info@ozergulplastik.com">info@ozergulplastik.com</a><br>
                      <a href="mailto:sales@ozergulplastik.com">sales@ozergulplastik.com</a>
                    </p>
                  </div>
                </div>

                <div class="info-item">
                  <div class="info-icon">
                    <i class="pi pi-clock"></i>
                  </div>
                  <div class="info-details">
                    <h4>Çalışma Saatleri</h4>
                    <p>
                      Pazartesi - Cuma: 08:00 - 18:00<br>
                      Cumartesi: 09:00 - 14:00<br>
                      Pazar: Kapalı
                    </p>
                  </div>
                </div>
              </div>
            </template>
          </Card>

          <Card class="social-card">
            <template #title>
              <h3>Sosyal Medya</h3>
            </template>
            <template #content>
              <div class="social-links">
                <a href="#" class="social-link facebook">
                  <i class="pi pi-facebook"></i>
                  <span>Facebook</span>
                </a>
                <a href="#" class="social-link twitter">
                  <i class="pi pi-twitter"></i>
                  <span>Twitter</span>
                </a>
                <a href="#" class="social-link instagram">
                  <i class="pi pi-instagram"></i>
                  <span>Instagram</span>
                </a>
                <a href="#" class="social-link linkedin">
                  <i class="pi pi-linkedin"></i>
                  <span>LinkedIn</span>
                </a>
              </div>
            </template>
          </Card>
        </div>
      </div>

      <!-- Map Section -->
      <section class="map-section">
        <h2 class="section-title">Konumumuz</h2>
        <div class="map-container">
          <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3008.7923881383965!2d28.97638331541656!3d41.04618327929553!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab9b7d6e9f0a1%3A0x3e8c9c6e0d0f0d0f!2sIstanbul%2C%20Turkey!5e0!3m2!1sen!2str!4v1234567890123!5m2!1sen!2str"
            width="100%" 
            height="450" 
            style="border:0; border-radius: 12px;" 
            allowfullscreen="" 
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
          ></iframe>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const submitted = ref(false);
const loading = ref(false);

const formData = ref({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: ''
});

const subjects = ref([
  { label: 'Genel Bilgi', value: 'general' },
  { label: 'Ürün Bilgisi', value: 'product' },
  { label: 'Teklif Talebi', value: 'quote' },
  { label: 'Destek', value: 'support' },
  { label: 'Diğer', value: 'other' }
]);

const openWhatsApp = () => {
  // WhatsApp numarası - buraya gerçek numara eklenebilir
  const phoneNumber = '905551234567'; // Örnek numara
  const message = encodeURIComponent('Merhaba, Öz Ergül Plastik hakkında bilgi almak istiyorum.');
  window.open(`https://wa.me/${phoneNumber}?text=${message}`, '_blank');
};

const submitForm = async () => {
  submitted.value = true;

  // Validation
  if (!formData.value.name || !formData.value.email || !formData.value.phone || !formData.value.message) {
    toast.add({
      severity: 'warn',
      summary: 'Eksik Bilgi',
      detail: 'Lütfen zorunlu alanları doldurun',
      life: 3000
    });
    return;
  }

  loading.value = true;

  try {
    // CSRF token al
    await axios.get('/sanctum/csrf-cookie');
    
    const response = await axios.post('/api/public/contact-requests', {
      name: formData.value.name,
      email: formData.value.email,
      phone: formData.value.phone,
      subject: formData.value.subject || null,
      message: formData.value.message,
      type: 'contact'
    });

    toast.add({
      severity: 'success',
      summary: 'Başarılı',
      detail: 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.',
      life: 5000
    });

    // Reset form
    formData.value = {
      name: '',
      email: '',
      phone: '',
      subject: '',
      message: ''
    };
    submitted.value = false;
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Hata',
      detail: 'Bir hata oluştu. Lütfen tekrar deneyin.',
      life: 3000
    });
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped lang="scss">
.contact-page {
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

.contact-content {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 3rem;
  margin-bottom: 4rem;
  padding: 2rem 0;
  
  @media (max-width: 968px) {
    grid-template-columns: 1fr;
  }
}

// Contact Form
.form-card {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  background: white;
  border: none;
  
  :deep(.p-card-body) {
    padding: 2rem;
  }
  
  :deep(.p-card-title) {
    padding: 0;
    margin-bottom: 1.5rem;
  }
  
  :deep(.p-card-content) {
    padding: 0;
  }
  
  h2 {
    color: #1e293b;
    margin-bottom: 1.5rem;
  }
  
  .field {
    margin-bottom: 1.5rem;
    
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #334155;
    }
    
    :deep(.p-inputtext),
    :deep(.p-dropdown),
    :deep(.p-textarea) {
      background: white !important;
      color: #1e293b !important;
      border: 1px solid #E2E8F0 !important;
      
      &:enabled {
        background: white !important;
        color: #1e293b !important;
      }
      
      &:enabled:hover {
        border-color: #16A34A !important;
        background: white !important;
        color: #1e293b !important;
      }
      
      &:enabled:focus {
        border-color: #16A34A !important;
        box-shadow: 0 0 0 1px #16A34A !important;
        background: white !important;
        color: #1e293b !important;
      }
      
      &::placeholder {
        color: #94A3B8 !important;
      }
    }
    
    :deep(.p-dropdown-label) {
      background: white !important;
      color: #1e293b !important;
    }
    
    :deep(.p-dropdown-trigger) {
      color: #64748B !important;
    }
    
    :deep(.p-dropdown-label) {
      background: white !important;
      color: #1e293b !important;
    }
    
    :deep(.p-dropdown-trigger) {
      color: #64748B !important;
    }
  }
  
  .form-actions {
    margin-top: 2rem;
  }
}

// Contact Info
.contact-info-section {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.info-card {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  background: white;
  border: none;
  
  :deep(.p-card-body) {
    padding: 2rem;
  }
  
  :deep(.p-card-title) {
    padding: 0;
    margin-bottom: 1.5rem;
  }
  
  :deep(.p-card-content) {
    padding: 0;
  }
  
  h2 {
    color: #1e293b;
    margin-bottom: 1.5rem;
  }
}

.info-list {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.info-item {
  display: flex;
  gap: 1.5rem;
}

.info-icon {
  width: 60px;
  height: 60px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
  border-radius: 12px;
  color: white;
  
  i {
    font-size: 1.8rem;
  }
}

.info-details {
  h4 {
    font-size: 1.2rem;
    color: #1e293b;
    margin-bottom: 0.5rem;
  }
  
  p {
    color: #64748b;
    line-height: 1.8;
    
    a {
      color: #16A34A;
      text-decoration: none;
      transition: color 0.3s ease;
      
      &:hover {
        color: #22C55E;
        text-decoration: underline;
      }
    }
  }
}

// Social Card
.social-card {
  background: white;
  border: none;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  
  :deep(.p-card-body) {
    padding: 2rem;
  }
  
  :deep(.p-card-title) {
    padding: 0;
    margin-bottom: 1.5rem;
  }
  
  :deep(.p-card-content) {
    padding: 0;
  }
  
  h3 {
    color: #1e293b;
    margin-bottom: 1rem;
  }
}

.social-links {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.social-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  text-decoration: none;
  color: #334155;
  font-weight: 600;
  transition: all 0.3s ease;
  
  i {
    font-size: 1.5rem;
  }
  
  &:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  
  &.facebook:hover {
    color: #1877f2;
    border-left: 4px solid #1877f2;
  }
  
  &.twitter:hover {
    color: #1da1f2;
    border-left: 4px solid #1da1f2;
  }
  
  &.instagram:hover {
    color: #e4405f;
    border-left: 4px solid #e4405f;
  }
  
  &.linkedin:hover {
    color: #0077b5;
    border-left: 4px solid #0077b5;
  }
}

// Map Section
.map-section {
  margin-top: 4rem;
  padding: 2rem 0;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 2rem;
  text-align: center;
}

.map-container {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  overflow: hidden;
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
</style>

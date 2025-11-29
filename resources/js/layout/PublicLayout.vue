<template>
  <div class="public-layout">
    <PublicHeader />
    
    <main class="main-content">
      <router-view v-slot="{ Component }">
        <transition name="page-fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
    
    <PublicFooter />
    
    <!-- Quote Dialog -->
    <Dialog 
      v-model:visible="showQuoteDialog" 
      modal 
      header="Teklif Talebi"
      :style="{ width: '600px' }"
      :breakpoints="{ '768px': '95vw' }"
    >
      <div class="quote-form">
        <div class="p-fluid">
          <div class="field">
            <label for="name">Ad Soyad *</label>
            <InputText id="name" v-model="quoteForm.name" placeholder="Adınız ve soyadınız" />
          </div>
          
          <div class="field">
            <label for="email">E-posta *</label>
            <InputText id="email" v-model="quoteForm.email" type="email" placeholder="ornek@email.com" />
          </div>
          
          <div class="field">
            <label for="phone">Telefon *</label>
            <InputText id="phone" v-model="quoteForm.phone" placeholder="+90 (555) 123 45 67" />
          </div>
          
          <div class="field">
            <label for="product">Ürün</label>
            <InputText id="product" v-model="quoteForm.product" placeholder="İlgilendiğiniz ürün" />
          </div>
          
          <div class="field">
            <label for="quantity">Miktar</label>
            <InputText id="quantity" v-model="quoteForm.quantity" placeholder="Tahmini miktar" />
          </div>
          
          <div class="field">
            <label for="message">Mesajınız</label>
            <Textarea 
              id="message" 
              v-model="quoteForm.message" 
              rows="4" 
              placeholder="Detayları belirtiniz..."
            />
          </div>
        </div>
      </div>
      
      <template #footer>
        <Button label="İptal" severity="secondary" @click="closeQuoteDialog" text />
        <Button label="Gönder" severity="success" @click="submitQuote" icon="pi pi-send" />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import PublicHeader from './PublicHeader.vue';
import PublicFooter from './PublicFooter.vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const showQuoteDialog = ref(false);

const quoteForm = ref({
  name: '',
  email: '',
  phone: '',
  product: '',
  quantity: '',
  message: ''
});

const closeQuoteDialog = () => {
  showQuoteDialog.value = false;
  resetForm();
};

const resetForm = () => {
  quoteForm.value = {
    name: '',
    email: '',
    phone: '',
    product: '',
    quantity: '',
    message: ''
  };
};

const submitQuote = async () => {
  // Basit validasyon
  if (!quoteForm.value.name || !quoteForm.value.email || !quoteForm.value.phone) {
    toast.add({
      severity: 'warn',
      summary: 'Eksik Bilgi',
      detail: 'Lütfen zorunlu alanları doldurun',
      life: 3000
    });
    return;
  }

  try {
    // CSRF token al
    await axios.get('/sanctum/csrf-cookie');
    
    const response = await axios.post('/api/public/contact-requests', {
      name: quoteForm.value.name,
      email: quoteForm.value.email,
      phone: quoteForm.value.phone,
      subject: quoteForm.value.product || null,
      message: quoteForm.value.message || null,
      type: 'quote',
      product: quoteForm.value.product || null,
      quantity: quoteForm.value.quantity || null
    });

    toast.add({
      severity: 'success',
      summary: 'Başarılı',
      detail: 'Teklif talebiniz alındı. En kısa sürede size dönüş yapacağız.',
      life: 5000
    });
    
    closeQuoteDialog();
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Hata',
      detail: 'Bir hata oluştu. Lütfen tekrar deneyin.',
      life: 3000
    });
  }
};
</script>

<style scoped lang="scss">
.public-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
  width: 100%;
  background: white;
}

// Page transitions
.page-fade-enter-active,
.page-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.page-fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.page-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.quote-form {
  padding: 1rem 0;
  
  .field {
    margin-bottom: 1.5rem;
    
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #334155;
    }
    
    :deep(.p-inputtext),
    :deep(.p-textarea) {
      background: white !important;
      color: #1e293b !important;
      border: 1px solid #E2E8F0 !important;
      
      &:enabled:hover {
        border-color: #16A34A !important;
      }
      
      &:enabled:focus {
        border-color: #16A34A !important;
        box-shadow: 0 0 0 1px #16A34A !important;
      }
      
      &::placeholder {
        color: #94A3B8 !important;
      }
    }
  }
}
</style>

<template>
  <footer class="public-footer">
    <div class="footer-content">
      <div class="footer-grid">
        <!-- Şirket Bilgileri -->
        <div class="footer-section">
          <h3 class="footer-title">Öz Ergül Plastik</h3>
          <p class="footer-desc">
            Geri dönüşüm sektöründe öncü, çevre dostu poşet üretimi yapan fabrikamız,
            kaliteli ve sürdürülebilir ürünlerle sektörde fark yaratıyor.
          </p>
          <div class="social-links">
            <a href="#" class="social-link" aria-label="Facebook">
              <i class="pi pi-facebook"></i>
            </a>
            <a href="#" class="social-link" aria-label="Twitter">
              <i class="pi pi-twitter"></i>
            </a>
            <a href="#" class="social-link" aria-label="Instagram">
              <i class="pi pi-instagram"></i>
            </a>
            <a href="#" class="social-link" aria-label="LinkedIn">
              <i class="pi pi-linkedin"></i>
            </a>
          </div>
        </div>

        <!-- Hızlı Linkler -->
        <div class="footer-section">
          <h4 class="footer-subtitle">Hızlı Linkler</h4>
          <ul class="footer-links">
            <li><router-link to="/">Anasayfa</router-link></li>
            <li><router-link to="/urunler">Ürünler</router-link></li>
            <li><router-link to="/hakkimizda">Hakkımızda</router-link></li>
            <li><router-link to="/iletisim">İletişim</router-link></li>
          </ul>
        </div>

        <!-- İletişim Bilgileri -->
        <div class="footer-section">
          <h4 class="footer-subtitle">İletişim</h4>
          <ul class="contact-info">
            <li>
              <i class="pi pi-phone"></i>
              <span>+90 (541) 431 49 43</span>
            </li>
            <li>
              <i class="pi pi-envelope"></i>
              <span>info@ozergulplastik.com</span>
            </li>
            <li>
              <i class="pi pi-map-marker"></i>
              <span>Modern Sanayi Sitesi Adana</span>
            </li>
            <li>
              <i class="pi pi-clock"></i>
              <span>7/24</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Copyright -->
      <div class="footer-bottom">
        <p>&copy; {{ currentYear }} Öz Ergül Plastik. Tüm hakları saklıdır.</p>
        <p class="footer-credits">Çevre dostu üretim ile geleceğe değer katıyoruz 🌱</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue';

const currentYear = computed(() => new Date().getFullYear());
</script>

<style scoped lang="scss">
.public-footer {
  background: #FFFFFF;
  color: #334155;
  margin-top: 0;
  border-top: 1px solid #E2E8F0;
  box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.05);
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 3rem 2rem 1.5rem;
}

.footer-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 3rem;
  margin-bottom: 3rem;

  @media (max-width: 768px) {
    gap: 2rem;
  }
}

.footer-section {
  h3, h4 {
    margin-bottom: 1rem;
  }
}

.footer-title {
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 1rem;
}

.footer-subtitle {
  font-size: 1.1rem;
  font-weight: 600;
  color: #16A34A;
}

.footer-desc {
  color: #64748B;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.social-links {
  display: flex;
  gap: 1rem;
}

.social-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #F0FDF4;
  color: #16A34A;
  transition: all 0.3s ease;

  &:hover {
    background: linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
  }

  i {
    font-size: 1.2rem;
  }
}

.footer-links {
  list-style: none;
  padding: 0;

  li {
    margin-bottom: 0.75rem;

    a {
      color: #64748B;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-block;

      &:hover {
        color: #16A34A;
        transform: translateX(5px);
      }
    }
  }
}

.contact-info {
  list-style: none;
  padding: 0;

  li {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1rem;
    color: #64748B;

    i {
      color: #16A34A;
      margin-top: 0.2rem;
      flex-shrink: 0;
    }
  }
}

.footer-bottom {
  border-top: 1px solid #E2E8F0;
  padding-top: 2rem;
  text-align: center;

  p {
    color: #64748B;
    margin: 0.5rem 0;
  }

  .footer-credits {
    font-size: 0.9rem;
    color: #94A3B8;
  }
}
</style>

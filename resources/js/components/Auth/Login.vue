<template>
    <div class="flex justify-center items-center min-h-screen bg-gray-100">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-3xl font-semibold mb-6 text-center">Giriş Yap</h2>
            <form @submit.prevent="login">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700">E-posta</label>
                    <input
                        type="email"
                        id="email"
                        v-model="email"
                        required
                        class="border border-gray-300 rounded p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700">Şifre</label>
                    <input
                        type="password"
                        id="password"
                        v-model="password"
                        required
                        class="border border-gray-300 rounded p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <button
                    type="submit"
                    class="bg-blue-500 text-white p-2 w-full rounded hover:bg-blue-600 transition-colors duration-300"
                >
                    Giriş Yap
                </button>
            </form>
        </div>
    </div>
</template>

<script>

export default {
    components : {

    },
    data() {
        return {
            email: '',
            password: ''
        };
    },
    methods: {
        login() {
            // Giriş işlemleri yapılacak (örneğin, API çağrısı)
            // Başarılıysa:
            this.$router.push('/');
        }
    }
};
</script>

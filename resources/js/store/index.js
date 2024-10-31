import Vuex from 'vuex'
import axios from "axios";
import router from "../router/index.js";


const state = {
    user: null,
    authenticated: null,
    users: [], // Kullanıcıları depolamak için bir dizi
}

const getters = {
    user: state => state.user,
    authenticated: state => state.authenticated,
    users: (state) => state.users, // Kullanıcılar için getter
}

const actions = {
    async authenticate({commit}) {
        if (!state.user)
            return axios.get('/api/user').then(res => {
                commit('setAuthenticate', true)
                commit('setUser', res.data)

            }).catch(err=>{

            })

    },
    async login({commit, dispatch, state}, authData) {

        await axios.get('/sanctum/csrf-cookie').then((resp) => {
            axios.post('/api/login', {...authData})
                .then(async res => {
                    commit('setAuthenticate', true)
                    commit('setUser', res.data.data)
                    await router.push('/sys')
                    console.log(state);
                }).catch((err) => {
                console.log(err)
            })
        })

    },

    async logout({commit, dispatch, state}) {


        await axios.get('/api/logout').then(async () => {
            commit('setUser', null);
            commit('setAuthenticate', false);
            await router.push('/login');
        });
    },
    // Kullanıcıları çekmek için action
    async fetchUsers({ commit }) {
        try {
            const response = await axios.get('/api/getAllUsers'); // Kullanıcıları çekecek API uç noktası
            commit('setUsers', response.data); // Kullanıcıları state'e kaydediyoruz
        } catch (error) {
            console.error('Kullanıcıları çekerken hata:', error);
        }
    },
    // Kullanıcı kaydetme
    async createUser({ dispatch }, userData) {
        try {
            await axios.post('/api/createUsers', userData);
            dispatch('fetchUsers');
        } catch (error) {
            console.error('Kullanıcı kaydetme hatası:', error);
        }
    },

    // Kullanıcı güncelleme
    async updateUser({ dispatch }, userData ) {
        try {
            console.log('store içerisi',userData);
            await axios.put(`/api/users/${userData.id}`, userData);
            dispatch('fetchUsers');

        } catch (error) {
            console.error('Kullanıcı güncelleme hatası:', error);
        }
    },

    // Kullanıcı silme
    async deleteUser({ dispatch }, id) {
        try {
            await axios.delete(`/api/users/${id}`);
            dispatch('fetchUsers');
        } catch (error) {
            console.error('Kullanıcı silme hatası:', error);
        }
    },
}
const mutations = {
    setAuthenticate(state, value) {
        state.authenticated = value
    },
    setUser(state, value) {
        state.user = value
    },
    setUsers(state, users) { // Kullanıcıları güncellemek için mutation
        state.users = users;
    },

}

const store = new Vuex.Store({state, getters, actions, mutations})

export default store

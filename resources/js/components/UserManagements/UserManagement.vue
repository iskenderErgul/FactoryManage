<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="New" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Delete" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedUsers || !selectedUsers.length" />
                </template>

            </Toolbar>

            <DataTable ref="dt" :value="users" v-model:selection="selectedUsers" dataKey="id"
                       :paginator="true" :rows="10" :filters="filters"
                       paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Mevcut {first} ile {last} arasında {totalRecords} kullanıcı">


                <template #header>
                    <div class="p-inputgroup">
                              <span class="p-inputgroup-addon">
                                    <i class="pi pi-search"></i>
                              </span>
                        <InputText v-model="filters['global'].value" placeholder="Search..." />
                    </div>
                </template>

                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>

                <Column field="id" header="Id" sortable style="min-width:6rem"></Column>

                <Column header="Image"  style="min-width:6rem">
                    <template #body="slotProps">
                        <Avatar icon="pi pi-user" class="mr-2" size="xlarge" shape="circle"   style="background-color: #ece9fc; color: #2a1261 "  />
                    </template>
                </Column>

                <Column field="name" header="Name" sortable style="min-width:16rem"></Column>

                <Column field="email" header="Email" sortable style="min-width:16rem"></Column>

                <Column field="role" header="Role" sortable style="min-width:16rem"></Column>

                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="edituser(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteuser(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />
        <Dialog v-model:visible="userDialog" :style="{width: '450px'}" header="User Details" :modal="true" class="p-fluid">
            <div class="field">
                <label for="image">Image</label>
                <FileUpload id="image" mode="basic" accept="image/*" :maxFileSize="1000000" label="Upload Image" chooseLabel="Choose Image" @upload="onImageUpload" />
            </div>
            <div class="field">
                <label for="name">Name</label>
                <InputText id="name" v-model.trim="user.name" required="true" autofocus :invalid="submitted && !user.name" />
                <small class="p-error" v-if="submitted && !user.name">Name is required.</small>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <InputText id="email" v-model.trim="user.email" required="true" :invalid="submitted && !user.email" />
                <small class="p-error" v-if="submitted && !user.email">Email is required.</small>
            </div>
            <div class="field">
                <label for="role">Role</label>
                <Dropdown id="role" v-model="user.role" :options="roles" optionLabel="name" required="true" :invalid="submitted && !user.role" />
                <small class="p-error" v-if="submitted && !user.role">Role is required.</small>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <div class="flex align-items-center">
                    <InputText id="password" v-model.trim="user.password" :type="showPassword ? 'text' : 'password'" :required="!user.id" :invalid="submitted && !user.password && !user.id" />
                    <Button :icon="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'" @click="togglePasswordVisibility" />
                </div>
                <small class="p-error" v-if="submitted && !user.password && !user.id">Password is required for new users.</small>
                <small class="p-info" v-if="user.id">Leave empty to keep current password.</small>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog"/>
                <Button label="Save" icon="pi pi-check" text @click="saveuser" />
            </template>
        </Dialog>


        <Dialog v-model:visible="deleteUserDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="user">Are you sure you want to delete <b>{{user.name}}</b>?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteUserDialog = false"/>
                <Button label="Yes" icon="pi pi-check" text @click="deleteuser" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteUsersDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="user">Are you sure you want to delete the selected users?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteUsersDialog = false"/>
                <Button label="Yes" icon="pi pi-check" text @click="deleteselectedUsers" />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import {ref, onMounted, computed} from 'vue';
import { useStore } from 'vuex';
import axios from "axios";
import { FilterMatchMode } from 'primevue/api';
import  Button  from 'primevue/button';
import  Toolbar  from 'primevue/toolbar';
import  DataTable  from 'primevue/datatable';
import  Column  from 'primevue/column';
import  FileUpload  from 'primevue/fileupload';
import  InputText  from 'primevue/inputtext';
import  Dialog  from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import  Toast from 'primevue/toast';
import Avatar from "primevue/avatar";


const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
const showPassword = ref(false);
const store = useStore();
const toast = ref(null);
const roles = ref([
    { name: 'Admin', code: 'admin' },
    { name: 'Worker', code: 'worker' },
]);
const dt = ref();
const users =computed(() => store.getters.users)
const userDialog = ref(false);
const deleteUserDialog = ref(false);
const deleteUsersDialog = ref(false);
const user = ref({});
const selectedUsers = ref();
const filters = ref({
    'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
});
const submitted = ref(false);

onMounted(() => {
    // getUser();
    store.dispatch('fetchUsers');
});
const openNew = () => {
    user.value = {};
    submitted.value = false;
    userDialog.value = true;
};
const hideDialog = () => {
    userDialog.value = false;
    submitted.value = false;
};
const saveuser = () => {
    submitted.value = true;

    if (user.value.name && user.value.name.trim()) {
        if (user.value.id) {
            // Kullanıcı düzenleniyor
            console.log('component içerisi',user.value);
            store.dispatch('updateUser', {
                ...user.value,
            });
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Kullanıcı başarıyla Güncellendi!', life: 3000 });
        } else {
            // Yeni kullanıcı ekleniyor - şifre zorunlu
            if (!user.value.password || !user.value.password.trim()) {
                toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Yeni kullanıcı için şifre zorunludur!', life: 3000 });
                return;
            }
            user.value.image = 'user-placeholder.svg';
            user.value.role = user.value.role.name;
            store.dispatch('createUser', user.value);
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Kullanıcı başarıyla eklendi!', life: 3000 });
        }

        userDialog.value = false;
        user.value = {};
    } else {
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Kullanıcı adı boş olamaz!', life: 3000 });
    }
};

const edituser = (prod) => {
    user.value = {
        ...prod,
        password: '' // Şifre alanını boş bırak
    };
    userDialog.value = true;
};
const confirmDeleteuser = (prod) => {
    user.value = prod;
    deleteUserDialog.value = true;
};
const deleteuser = () => {
    store.dispatch('deleteUser', user.value.id);
    deleteUserDialog.value = false;
    user.value = {};
    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Kullanıcı başarıyla silindi!', life: 3000 });
};
const confirmDeleteSelected = () => {
    deleteUsersDialog.value = true;
};
const deleteselectedUsers = () => {
    selectedUsers.value.forEach(selectedUser => {
        store.dispatch('deleteUser', selectedUser.id);
    });
    deleteUsersDialog.value = false;
    selectedUsers.value = null;
    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Seçilen kullanıcılar başarıyla silindi!', life: 3000 });
};
const onImageUpload = (event) => {
    const uploadedFiles = event.files;
    if (uploadedFiles.length) {
        user.value.image = uploadedFiles[0].name;
    }
};

</script>



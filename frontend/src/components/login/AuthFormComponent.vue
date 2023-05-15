<template>
  <q-form
    @submit.prevent="onSubmit"
    class="q-gutter-md"
    style="width: 400px; margin-left: 0px"
  >
    <q-input
      outlined
      class="q-mb-lg"
      type="email"
      label="Пошта"
      v-model="userData.email"
    />
    <q-input
      outlined
      v-model="userData.password"
      class="q-mb-lg"
      :type="isPwd ? 'password' : 'text'"
      label="Пароль"
    >
      <template v-slot:append>
        <q-icon
          :name="isPwd ? 'visibility_off' : 'visibility'"
          class="cursor-pointer"
          @click="isPwd = !isPwd"
        />
      </template>
    </q-input>
    <div class="col-12 flex justify-center">
      <q-btn
        color="primary"
        label="Увійти"
        :loading="isLoading"
        style="width: 50%"
        type="submit"
      />
    </div>
  </q-form>
</template>
<script setup>
import { ref, reactive } from "vue";
import { useQuasar } from "quasar";
import { useUserStore } from "src/stores/userStore";
import { useRouter } from "vue-router";

const $q = useQuasar();
const store = useUserStore();
const router = useRouter();

let notification = null;

let isPwd = ref(true);
let isLoading = ref(false);
let userData = reactive({
  email: "",
  password: "",
});

function onSubmit() {
  isLoading.value = true;
  store
    .login(userData)
    .then((res) => {
      userData = {
        email: res.data.user.email,
        name: res.data.user.name,
        token: res.data.auth.token,
        expired_at: res.data.auth.expired_at,
        allowenses: res.data.allowenses,
      };
      sessionStorage.setItem("data", JSON.stringify(userData));
      router.push("/");
      if (notification != null) {
        notification();
      }
    })
    .catch((error) => {
      isLoading.value = false;

      notification = $q.notify({
        position: "top",
        color: "negative",
        message: error.response.data,
        group: true,
      });
    });
}
</script>
<style scoped></style>

<template>
  <div class="container flex items-center justify-center column">
    <Logo />
    <AuthForm />
  </div>
</template>
<script setup>
import Logo from "../components/login/LogoComponent.vue";
import AuthForm from "../components/login/AuthFormComponent.vue";
</script>
<style scoped>
.container {
  width: 100%;
  height: 100vh;
}
</style>

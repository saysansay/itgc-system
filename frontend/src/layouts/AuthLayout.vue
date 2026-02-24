<template>
  <div class="auth-layout">
    <router-view />
  </div>
</template>

<script setup>
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  background: linear-gradient(135deg, #e60000 0%, #cc0000 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>

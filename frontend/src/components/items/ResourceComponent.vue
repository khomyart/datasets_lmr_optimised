<template>
  <div class="row col-12 q-mt-md">
    <div class="header q-pa-sm">
      <a
        target="_blank"
        :href="`${props.appStore.appURL}/dataset/${props.datasetId}/resource/${props.item.id}`"
        >{{ props.item.name }}</a
      >
    </div>
    <div class="content">
      <div class="row q-mt-md">
        <div class="row col-12 items-center q-mb-md">
          <div class="q-mr-sm">Статус:</div>
          <div
            class="q-mr-md"
            :class="{
              'custom-badge-success': props.item.state == 'active',
              'custom-badge-fail': props.item.state != 'active',
            }"
          >
            {{ state }}
          </div>

          <div class="q-mr-sm" v-if="validationStatus != ''">Валідація:</div>
          <div
            class="q-mr-md"
            :class="{
              'custom-badge-success': props.item.validation_status == 'success',
              'custom-badge-fail': props.item.validation_status == 'failure',
            }"
          >
            {{ validationStatus }}
          </div>

          <div class="q-mr-sm" v-if="props.item.format != ''">
            Формат: {{ props.item.format }}
          </div>
        </div>
        <div class="row col-12 q-mb-md" v-if="props.item.description != ''">
          <div class="row col-12 q-mb-sm" style="text-decoration: underline">
            Опис:
          </div>
          <div class="row col-12 q-mb-sm">
            <div>
              {{ props.item.description }}
            </div>
          </div>
        </div>
        <div class="row col-12 q-mb-sm" v-if="props.item.url != ''">
          <div class="row col-12">
            <a :href="props.item.url">Завантажити</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { computed } from "vue";

const props = defineProps(["item", "appStore", "datasetId"]);

const state = computed(() => {
  return props.item.state == "active" ? "активний" : "неактивний";
});

const validationStatus = computed(() => {
  let validationStatus = "";
  switch (props.item.validation_status) {
    case "success":
      validationStatus = "успішна";
      break;
    case "failure":
      validationStatus = "невдала";
      break;

    default:
      break;
  }
  return validationStatus;
});
</script>
<style scoped>
.header {
  font-weight: bold;
}
.content {
  border-left: 2px solid rgb(177, 15, 161);
  border-top: 2px solid rgb(177, 15, 161);
  padding: 5px 0px 0px 20px;
}

a {
  text-decoration: none;
  color: black;
  transition: all 0.2s ease-in-out;
}
a:hover {
  color: blue;
}

.custom-badge-success {
  background-color: #4caf50;
  color: white;
  padding: 2px 10px 2px;
  width: fit-content;
  border-radius: 10px;
  font-size: 12px;
}
.custom-badge-fail {
  background-color: #f44336;
  color: white;
  padding: 2px 10px 2px;
  width: fit-content;
  border-radius: 10px;
  font-size: 12px;
}
.custom-badge-warning {
  background-color: #ff9800;
  color: white;
  padding: 2px 10px 2px;
  width: fit-content;
  border-radius: 10px;
  font-size: 12px;
}

.custom-badge-inactive {
  background-color: rgb(145, 145, 145);
  color: white;
  padding: 2px 10px 2px;
  width: fit-content;
  border-radius: 10px;
  font-size: 12px;
}
</style>

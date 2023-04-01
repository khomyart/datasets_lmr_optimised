<template>
  <div class="row col-7 q-mb-md organization">
    <div class="header q-pa-sm">
      <a
        target="_blank"
        :href="`${props.appStore.appURL}/organization/${props.item.name}`"
        >{{ props.item.display_name }}</a
      >
    </div>

    <div class="content">
      <template v-for="(item, index) in props.item.datasets" :key="index">
        <DatasetComponent :item="item" :appStore="props.appStore" />
      </template>
    </div>
  </div>
</template>
<script setup>
import DatasetComponent from "src/components/items/DatasetComponent.vue";
const props = defineProps(["item", "appStore"]);
</script>
<style scoped>
.organization {
  height: fit-content;
}
.header {
  font-weight: bold;
  font-size: 16px;
}
.content {
  border-left: 2px solid rgb(1, 0, 28);
  border-top: 2px solid rgb(1, 0, 28);
  padding: 5px 0px 0px 20px;
  height: fit-content;
}

a {
  text-decoration: none;
  color: black;
  transition: all 0.2s ease-in-out;
}
a:hover {
  color: blue;
}
</style>

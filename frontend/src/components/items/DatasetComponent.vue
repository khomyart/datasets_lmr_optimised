<template>
  <div class="row col-12 q-mt-md">
    <div class="header q-pa-sm">
      <a
        target="_blank"
        :href="`${props.appStore.appURL}/dataset/${props.item.id}`"
        >{{ props.item.title }}</a
      >
    </div>
    <div class="content">
      <div class="row q-mt-md">
        <div class="row col-12 items-center">
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

          <div class="q-mr-sm">Тип:</div>
          <div
            class="q-mr-md"
            :class="{
              'custom-badge-success': props.item.type == 'normal',
              'custom-badge-warning': props.item.type == 'reminder',
              'custom-badge-fail': props.item.type == 'debtor',
              'custom-badge-inactive': props.item.type == 'inactive',
            }"
          >
            {{ type }}
          </div>
          <div class="q-mr-sm">
            <a
              target="_blank"
              :href="`${appStore.appURL}/api/3/action/package_search?q=id:${props.item.id}`"
              >API</a
            >
          </div>
        </div>
        <div
          class="row col-12 q-mt-md"
          v-if="props.item.description != props.item.title"
        >
          <div class="row col-12 q-mb-sm" style="text-decoration: underline">
            Опис:
          </div>
          <div class="row col-12 q-mb-sm">
            <div>
              {{ props.item.description }}
            </div>
          </div>
        </div>
        <div class="row col-12 q-mt-md" v-if="props.item.purpose != ''">
          <div class="row col-12 q-mb-sm" style="text-decoration: underline">
            Підстава та призначення:
          </div>
          <div class="row col-12 q-mb-sm">
            <div>
              {{ props.item.purpose }}
            </div>
          </div>
        </div>
        <div class="row col-12 q-mt-md">
          <div class="row col-12 q-mb-sm" style="text-decoration: underline">
            Оновлення:
          </div>
          <div class="row col-12 q-mb-sm">
            <div class="col-5">Останнє: {{ props.item.last_updated_at }}</div>

            <div class="col-5">Частота: {{ updateFrequency }}</div>
          </div>
          <div class="row col-12">
            <div class="col-5" v-if="props.item.type != 'inactive'">
              Наступне: {{ props.item.next_update_at }}
            </div>
            <div class="col-5">
              {{ nextUpdateDateLabel }}
              {{ Math.abs(props.item.days_to_update) }}
            </div>
          </div>
        </div>
        <div class="row col-12 q-mt-md">
          <div class="row col-12 q-mb-sm" style="text-decoration: underline">
            Відповідальна особа:
          </div>
          <div class="row col-12 q-mb-sm">
            <div class="col-12 q-mb-sm">
              Ім'я: {{ props.item.maintainer_name }}
            </div>
            <div class="col-12">
              Електронна адерса:
              <a :href="`mailto:${props.item.maintainer_email}`">{{
                props.item.maintainer_email
              }}</a>
            </div>
          </div>
        </div>
      </div>
      <!-- <div class="row col-12 q-mt-sm" style="text-decoration: underline">
        Ресурси:
      </div> -->
      <template v-for="(item, index) in props.item.resources" :key="index">
        <ResourceComponent
          :item="item"
          :appStore="props.appStore"
          :datasetId="props.item.id"
        />
      </template>
    </div>
  </div>
</template>
<script setup>
import ResourceComponent from "./ResourceComponent.vue";
import { computed } from "vue";

const props = defineProps(["item", "appStore"]);
const state = computed(() => {
  return props.item.state == "active" ? "активний" : "неактивний";
});
const type = computed(() => {
  let type;
  switch (props.item.type) {
    case "normal":
      type = "звичайний";
      break;
    case "reminder":
      type = "нагадування";
      break;
    case "debtor":
      type = "протермінований";
      break;
    case "inactive":
      type = "не оновлюється";
      break;

    default:
      break;
  }
  return type;
});
const nextUpdateDateLabel = computed(() => {
  let nextUpdateDateLabel = "Днів до кінцевого терміну:";

  if (props.item.type == "debtor") {
    nextUpdateDateLabel = "Днів протерміновано:";
  }

  if (props.item.update_frequency == "more than once a day") {
    nextUpdateDateLabel = "Днів не оновлюється:";
  }

  return nextUpdateDateLabel;
});
const updateFrequency = computed(() => {
  let updateFrequency;
  switch (props.item.update_frequency) {
    case "immediately after making changes":
      updateFrequency = "одразу після внесення змін";
      break;
    case "more than once a day":
      updateFrequency = "більше одного разу на день";
      break;
    case "once a day":
      updateFrequency = "щодня";
      break;
    case "once a week":
      updateFrequency = "щотижня";
      break;
    case "once a month":
      updateFrequency = "щомісяця";
      break;
    case "once a quarter":
      updateFrequency = "щокварталу";
      break;
    case "once a half year":
      updateFrequency = "щопівроку";
      break;
    case "once a year":
      updateFrequency = "раз на рік";
      break;
    case "no longer updated":
      updateFrequency = "більше не оновлюється";
      break;
    default:
      break;
  }
  return updateFrequency;
});
</script>
<style scoped>
.header {
  font-weight: bold;
}
.content {
  border-left: 2px solid rgb(15, 31, 177);
  border-top: 2px solid rgb(15, 31, 177);

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

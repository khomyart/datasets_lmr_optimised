<template>
  <q-btn flat icon="filter_list" round class="filter-button">
    <q-badge
      style="position: absolute; top: 5px; left: 30px"
      rounded
      color="red"
      v-if="
        appStore.filters.data.datasets.selectedParams.executive_authority_name
          .value != '' ||
        appStore.filters.data.datasets.selectedParams.dataset_title.value !=
          '' ||
        appStore.filters.data.datasets.selectedParams.resource_name.value != ''
      "
    />
    <q-tooltip anchor="bottom middle" :offset="[0, 0]" class="text-body2">
      Фільтр
    </q-tooltip>
    <q-menu self="bottom middle" :offset="[-125, -55]">
      <q-inner-loading :showing="datasetStore.data.isItemsLoading">
        <q-spinner-puff size="50px" color="primary" />
      </q-inner-loading>
      <div style="min-width: 250px; min-height: fit-content">
        <div class="row justify-end q-mb-sm">
          <q-btn flat v-close-popup dense icon="close"></q-btn>
        </div>
        <div class="row">
          <div class="filter-body col-12 q-px-md">
            <q-input
              class="col-12 q-mb-sm"
              outlined
              placeholder="Назва організації"
              dense
              debounce="700"
              v-model="
                appStore.filters.data.datasets.selectedParams
                  .executive_authority_name.value
              "
            />
            <q-select
              class="col-12 q-mb-md"
              dense
              outlined
              v-model="
                appStore.filters.data.datasets.selectedParams
                  .executive_authority_name.filterMode
              "
              :options="appStore.filters.availableParams.items"
              @update:model-value="
                $emit('changeFilterMode', 'executive_authority_name')
              "
            >
            </q-select>
            <q-input
              class="col-12 q-mb-sm"
              outlined
              placeholder="Назва набору даних"
              dense
              debounce="700"
              v-model="
                appStore.filters.data.datasets.selectedParams.dataset_title
                  .value
              "
            />
            <q-select
              class="col-12 q-mb-md"
              dense
              outlined
              v-model="
                appStore.filters.data.datasets.selectedParams.dataset_title
                  .filterMode
              "
              :options="appStore.filters.availableParams.items"
              @update:model-value="$emit('changeFilterMode', 'dataset_name')"
            >
            </q-select>
            <q-input
              class="col-12 q-mb-sm"
              outlined
              placeholder="Назва ресурсу"
              dense
              debounce="700"
              v-model="
                appStore.filters.data.datasets.selectedParams.resource_name
                  .value
              "
            />
            <q-select
              class="col-12 q-mb-md"
              dense
              outlined
              v-model="
                appStore.filters.data.datasets.selectedParams.resource_name
                  .filterMode
              "
              :options="appStore.filters.availableParams.items"
              @update:model-value="$emit('changeFilterMode', 'resource_name')"
            >
            </q-select>
            <div class="row q-mb-md">
              <q-btn v-close-popup class="col-12" @click="clearAllFilters"
                >Скинути всі фільтри</q-btn
              >
            </div>
          </div>
        </div>
      </div>
    </q-menu>
  </q-btn>
</template>
<script setup>
import { computed } from "vue";
import { useAppConfigStore } from "src/stores/appConfigStore";
import { useDatasetStore } from "src/stores/datasetStore";

const appStore = useAppConfigStore();
const datasetStore = useDatasetStore();

const emits = defineEmits(["changeFilterMode"]);

function clearAllFilters() {
  let filter = appStore.filters.data.datasets;

  for (const [key] of Object.entries(filter.selectedParams)) {
    if (key != "mode") {
      filter.selectedParams[key].value = "";
      filter.selectedParams[key].filterMode =
        appStore.filters.availableParams.items[0];
    }
  }
}
</script>
<style></style>

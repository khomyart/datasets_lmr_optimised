<template>
  <q-layout view="lHh Lpr lff" container style="height: 100vh">
    <q-header class="bg-white">
      <q-toolbar class="text-primary q-ml-sm">
        <FilterComponent @change-filter-mode="onChangedFieldFilterMode" />
        <q-btn flat icon="receipt_long" round class="filter-button q-ml-sm">
          <q-tooltip anchor="bottom middle" :offset="[0, 0]" class="text-body2">
            Сформувати звіт
          </q-tooltip>
        </q-btn>
        <q-btn
          flat
          icon="mail"
          round
          class="filter-button q-ml-sm"
          :loading="store.dataset.dialogs.email.isConfirmationLoading"
          :disable="store.dataset.dialogs.email.isConfirmationLoading"
        >
          <q-tooltip anchor="bottom middle" :offset="[0, 0]" class="text-body2">
            Розсилка
          </q-tooltip>
          <q-menu self="bottom middle" :offset="[-24, -55]">
            <q-inner-loading
              :showing="store.dataset.dialogs.email.isConfirmationLoading"
            >
              <q-spinner-puff size="50px" color="primary" />
            </q-inner-loading>

            <q-list style="width: 150px">
              <q-item
                v-close-popup
                v-ripple
                clickable
                @click="showEmailSendDialog('reminder')"
              >
                <q-item-section>Нагадування</q-item-section>
              </q-item>
              <q-item
                v-close-popup
                v-ripple
                clickable
                @click="showEmailSendDialog('debtor')"
              >
                <q-item-section>Боржники</q-item-section>
              </q-item>
              <q-separator></q-separator>
            </q-list>
          </q-menu>
        </q-btn>
        <q-btn
          flat
          icon="info"
          round
          class="filter-button q-ml-sm"
          :loading="store.dataset.data.isItemsLoading"
          :disable="store.dataset.data.isItemsLoading"
        >
          <q-tooltip anchor="bottom middle" :offset="[0, 0]" class="text-body2">
            Статистика
          </q-tooltip>
          <q-menu self="bottom middle" :offset="[-22, -55]">
            <div style="min-height: fit-content" class="q-px-md q-py-sm">
              <div class="row">
                <div class="flex-center">
                  <table>
                    <tr style="text-align: center">
                      <td colspan="2">Актуальна</td>
                    </tr>
                    <tr height="3">
                      <td width="110"></td>
                      <td width="35"></td>
                    </tr>
                    <tr>
                      <td>Організацій:</td>
                      <td>
                        {{
                          store.dataset.statistic.actual.executive_authorities
                        }}
                      </td>
                    </tr>
                    <tr>
                      <td>Наборів даних:</td>
                      <td>{{ store.dataset.statistic.actual.datasets }}</td>
                    </tr>
                    <tr>
                      <td>Ресурсів:</td>
                      <td>{{ store.dataset.statistic.actual.resources }}</td>
                    </tr>
                    <tr style="text-align: center">
                      <td colspan="2"><q-separator></q-separator></td>
                    </tr>
                    <tr>
                      <td>Боржників:</td>
                      <td>
                        {{ store.dataset.statistic.actual.debtors }}
                      </td>
                    </tr>
                    <tr>
                      <td>Нагадуваннь:</td>
                      <td>{{ store.dataset.statistic.actual.reminders }}</td>
                    </tr>
                    <tr>
                      <td>Неактивних:</td>
                      <td>{{ store.dataset.statistic.actual.inactives }}</td>
                    </tr>
                  </table>
                </div>
                <q-separator vertical class="q-mx-md"></q-separator>
                <div class="flex-center">
                  <table>
                    <tr style="text-align: center">
                      <td colspan="2">Загальна</td>
                    </tr>
                    <tr height="3">
                      <td width="110"></td>
                      <td width="35"></td>
                    </tr>
                    <tr>
                      <td>Організацій:</td>
                      <td>
                        {{
                          store.dataset.statistic.initial.executive_authorities
                        }}
                      </td>
                    </tr>
                    <tr>
                      <td>Наборів даних:</td>
                      <td>{{ store.dataset.statistic.initial.datasets }}</td>
                    </tr>
                    <tr>
                      <td>Ресурсів:</td>
                      <td>{{ store.dataset.statistic.initial.resources }}</td>
                    </tr>
                    <tr style="text-align: center">
                      <td colspan="2"><q-separator></q-separator></td>
                    </tr>
                    <tr>
                      <td>Боржників:</td>
                      <td>
                        {{ store.dataset.statistic.initial.debtors }}
                      </td>
                    </tr>
                    <tr>
                      <td>Нагадуваннь:</td>
                      <td>{{ store.dataset.statistic.initial.reminders }}</td>
                    </tr>
                    <tr>
                      <td>Неактивних:</td>
                      <td>{{ store.dataset.statistic.initial.inactives }}</td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
          </q-menu>
        </q-btn>
        <q-separator vertical class="q-ml-md"></q-separator>
        <q-btn
          flat
          icon="download"
          round
          class="filter-button q-ml-md"
          @click="store.dataset.read()"
          :loading="store.dataset.data.isItemsLoading"
        >
          <q-tooltip anchor="bottom middle" :offset="[0, 0]" class="text-body2">
            Отримати данні
          </q-tooltip>
        </q-btn>
        <q-space></q-space>
        <span class="text-black q-mr-sm">
          {{ store.users.data.name }}
        </span>
        <q-btn
          color="primary"
          icon="logout"
          flat
          round
          class="q-mr-sm"
          @click="logout"
        >
          <q-tooltip anchor="bottom middle" :offset="[0, 0]" class="text-body2">
            Вихід
          </q-tooltip>
        </q-btn>
      </q-toolbar>
      <q-separator inset />
    </q-header>

    <q-drawer show-if-above :width="250" :breakpoint="0">
      <q-scroll-area class="fit">
        <q-inner-loading
          :showing="
            store.dataset.data.isItemsLoading ||
            store.dataset.data.isReceivingItemsLoading
          "
        >
          <q-spinner-puff size="50px" color="primary" />
        </q-inner-loading>
        <div class="row flex-center q-my-sm">
          <img
            width="150"
            style="user-select: none"
            src="../assets/lutsk_logo.jpg"
            alt=""
          />
        </div>
        <q-separator></q-separator>
        <div class="row flex-center" style="height: 30px">
          <span class="q-ml-md"
            >Станом на: {{ store.dataset.statistic.initial.created_at }}</span
          >
          <q-btn
            class="q-ml-sm"
            round
            flat
            size="xs"
            icon="refresh"
            :loading="store.dataset.data.isReceivingItemsLoading"
            @click="store.dataset.receive"
          >
            <q-tooltip
              anchor="bottom middle"
              :offset="[0, 8]"
              class="text-body2"
            >
              Оновити
            </q-tooltip>
          </q-btn>
        </div>
        <q-separator></q-separator>

        <q-list padding class="menu-list">
          <template v-for="(menuItem, index) in menuItems" :key="index">
            <q-item
              @click="menuItem.onClick()"
              :active="
                menuItem.mode ==
                store.app.filters.data.datasets.selectedParams.mode
              "
              clickable
              v-ripple
            >
              <q-item-section avatar>
                <q-icon :name="menuItem.icon" />
              </q-item-section>

              <q-item-section> {{ menuItem.name }} </q-item-section>
            </q-item>
          </template>
        </q-list>
      </q-scroll-area>
    </q-drawer>

    <q-page-container>
      <q-page>
        <div class="page-holder">
          <div
            class="items-container row q-px-xl q-pb-xl q-pt-md"
            style="min-width: 800px"
          >
            <template v-for="(item, index) in store.dataset.items" :key="index">
              <OrganizationComponent :item="item" :appStore="store.app" />
            </template>
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>

  <!-- MAIL DIALOG -->
  <q-dialog
    persistent
    v-model="store.dataset.dialogs.email.isConfirmationShown"
    transition-show="scale"
    transition-hide="scale"
  >
    <q-card style="width: 420px">
      <q-card-section>
        <div class="text-h6 flex items-center">
          <q-icon size="md" class="q-mr-sm" name="mail" color="black"></q-icon>
          Розсилка ({{ mailSendTypeUkr }})
        </div>
      </q-card-section>
      <q-separator></q-separator>
      <q-form @submit="sendEmail">
        <q-card-section class="q-pt-md" style="text-align: justify">
          Якщо Ви натисните "Гаразд", буде створена розсилка на основі даних,
          які були зібрані
          <b>{{ store.dataset.statistic.initial.created_at }}</b
          >. Бажаєте продовжити?
        </q-card-section>
        <q-separator></q-separator>
        <q-card-actions align="right">
          <q-btn flat color="black" v-close-popup><b>Відміна</b></q-btn>
          <q-btn flat color="primary" type="submit"><b>Гаразд</b></q-btn>
        </q-card-actions>
      </q-form>
    </q-card>
  </q-dialog>

  <!-- MAIL REPORT DIALOG -->
  <q-dialog
    v-model="store.dataset.dialogs.email.isReportShown"
    transition-show="scale"
    transition-hide="scale"
  >
    <q-card style="width: 500px">
      <q-card-section>
        <div class="text-h6 flex items-center">
          <q-icon size="md" class="q-mr-sm" name="mail" color="black"></q-icon>
          Звіт про відправку ({{ mailSendTypeUkr }})
        </div>
      </q-card-section>
      <q-separator></q-separator>
      <q-card-section
        class="q-pt-md scroll"
        style="max-height: 50vh; text-align: justify"
      >
        <div v-if="store.dataset.emailReport.success.length != 0">
          <div class="row col-12">Успішно:</div>
          <div class="row col-12">
            <ul>
              <li
                v-for="(user, index) in store.dataset.emailReport.success"
                :key="index"
              >
                <b>{{ user.name }}</b> (<a
                  class="dialog-link"
                  :href="`mailto:${user.email}`"
                  >{{ user.email }}</a
                >)
                <ol>
                  <li v-for="(dataset, index) in user.datasets" :key="index">
                    {{ dataset.title }}
                  </li>
                </ol>
              </li>
            </ul>
          </div>
        </div>
        <div v-if="store.dataset.emailReport.error.length != 0">
          <div class="row col-12 q-mt-md">Невдало:</div>
          <div class="row col-12">
            <ul>
              <li
                v-for="(user, index) in store.dataset.emailReport.error"
                :key="index"
              >
                <b>{{ user.name }}</b> (<a
                  class="dialog-link"
                  :href="`mailto:${user.email}`"
                  >{{ user.email }}</a
                >)
                <ol>
                  <li v-for="(dataset, index) in user.datasets" :key="index">
                    {{ dataset.title }}
                  </li>
                </ol>
              </li>
            </ul>
          </div>
        </div>
      </q-card-section>
      <q-separator></q-separator>
      <q-card-actions align="right">
        <q-btn flat color="black" v-close-popup><b>Закрити</b></q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch, computed, onBeforeMount } from "vue";
import { useRouter } from "vue-router";
import { useUserStore } from "src/stores/userStore";
import { useAppConfigStore } from "src/stores/appConfigStore";
import { useDatasetStore } from "src/stores/datasetStore";
import OrganizationComponent from "src/components/items/OrganizationComponent.vue";
import FilterComponent from "src/components/FilterComponent.vue";

const router = useRouter();

const store = {
  users: useUserStore(),
  app: useAppConfigStore(),
  dataset: useDatasetStore(),
};

const menuItems = [
  {
    name: "Усі",
    icon: "reorder",
    mode: "all",
    onClick: () => {
      store.app.filters.data.datasets.selectedParams.mode = "all";
    },
  },
  {
    name: "Нагадування",
    icon: "schedule",
    mode: "reminder",
    onClick: () => {
      store.app.filters.data.datasets.selectedParams.mode = "reminder";
    },
  },
  {
    name: "Боржники",
    icon: "event_busy",
    mode: "debtor",
    onClick: () => {
      store.app.filters.data.datasets.selectedParams.mode = "debtor";
    },
  },
];

let mailSendType = ref("");

const mailSendTypeUkr = computed(() => {
  let temp;
  switch (mailSendType.value) {
    case "debtor":
      temp = "боржники";
      break;
    case "reminder":
      temp = "нагадування";
      break;
    default:
      break;
  }
  return temp;
});

function logout() {
  store.users.logout().finally(() => {
    router.push("/login");
    sessionStorage.removeItem("data");
    store.users.data.email = "";
    store.users.data.name = "";
    store.users.data.token.value = null;
    store.users.data.token.expiredAt = "";
  });
}

function onChangedFieldFilterMode(field) {
  if (store.app.filters.data.datasets.selectedParams[field].value != "") {
    store.dataset.read();
  }
}

function showEmailSendDialog(mailType) {
  store.dataset.dialogs.email.isConfirmationShown = true;
  mailSendType.value = mailType;
}

function sendEmail() {
  store.dataset.sendMail(mailSendType.value);
}

watch(
  [
    () => store.app.filters.data.datasets.selectedParams.mode,
    () =>
      store.app.filters.data.datasets.selectedParams.executive_authority_name
        .value,
    () => store.app.filters.data.datasets.selectedParams.dataset_title.value,
    () => store.app.filters.data.datasets.selectedParams.resource_name.value,
  ],
  () => {
    store.dataset.read();
  }
);

onBeforeMount(() => {
  let userData = JSON.parse(sessionStorage.getItem("data"));

  if (typeof userData === "object") {
    store.users.data.email = userData.email;
    store.users.data.name = userData.name;
    store.users.data.token.expiredAt = userData.expired_at;
    store.users.data.token.value = userData.token;
  }

  store.dataset.receive();
});
</script>

<style scoped>
.menu-list .q-item {
  border-radius: 0 10px 10px 0;
}
.menu-header {
  font-size: 17px;
}
.toolbar-icon-number {
  font-size: 17px;
  margin-right: 5px;
}

.toolbar-tooltip-content {
  background: white;
  color: black;
  padding: 2px;
  font-size: 14px;
  min-width: 250px;
}
.toolbar-tooltip-content-header {
  font-size: 1.2em;
  padding: 5px 0px 0px 10px;
}

.page-holder {
  height: calc(100vh - 51px);
  /* max-height: calc(100vh - 51px); 50px - height of toolbar */
}

.items-container {
  overflow: auto;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
}

ul {
  padding-left: 15px;
  margin-top: 0;
}

ol {
  padding-left: 25px;
}

ol > li {
  text-align: justify;
}

ul > li {
  text-align: start;
  margin-top: 10px;
}

.dialog-link {
  text-decoration: none;
  color: blue;
  transition: all 0.2s ease-in-out;
}
.dialog-link:hover {
  color: red;
}

/* animations */
.title-appearing-enter-from,
.title-appearing-leave-to {
  opacity: 0;
}
.title-appearing-enter-to,
.title-appearing-leave-from {
  opacity: 1;
}
.title-appearing-enter-active,
.title-appearing-leave-active {
  transition: all 0.5s ease-in-out;
}
</style>

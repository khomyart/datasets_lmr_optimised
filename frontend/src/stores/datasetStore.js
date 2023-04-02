import { defineStore } from "pinia";
import { api } from "src/boot/axios";
import { useAppConfigStore } from "./appConfigStore";
const appConfigStore = useAppConfigStore();

const sectionName = "datasets";

export const useDatasetStore = defineStore("dataset", {
  state: () => ({
    items: [],
    statistic: {
      initial: {
        created_at: "",
        executive_authorities: "",
        datasets: "",
        resources: "",
        debtors: "",
        reminders: "",
        inactives: "",
      },
      actual: {
        executive_authorities: "",
        datasets: "",
        resources: "",
        debtors: "",
        reminders: "",
        inactives: "",
      },
    },
    emailReport: [],
    dialogs: {
      email: {
        isConfirmationShown: false,
        isConfirmationLoading: false,
        isReportShown: false,
      },
      report: {
        isConfirmationShown: false,
        isConfirmationLoading: false,
      },
    },
    data: {
      isItemsLoading: false,
      isReceivingItemsLoading: false,
    },
  }),
  getters: {},
  actions: {
    read() {
      // this.items = [];
      this.data.isItemsLoading = true;
      console.log("read items");
      api
        .get(`/${sectionName}/read`, {
          params: {
            mode: appConfigStore.filters.data.datasets.selectedParams.mode,
            //executive_authority_name
            executive_authority_nameFilterValue:
              appConfigStore.filters.data[sectionName].selectedParams
                .executive_authority_name.value,
            executive_authority_nameFilterMode:
              appConfigStore.filters.data[sectionName].selectedParams
                .executive_authority_name.filterMode.value,
            //dataset_name
            dataset_titleFilterValue:
              appConfigStore.filters.data[sectionName].selectedParams
                .dataset_title.value,
            dataset_titleFilterMode:
              appConfigStore.filters.data[sectionName].selectedParams
                .dataset_title.filterMode.value,
            //resource_name
            resource_nameFilterValue:
              appConfigStore.filters.data[sectionName].selectedParams
                .resource_name.value,
            resource_nameFilterMode:
              appConfigStore.filters.data[sectionName].selectedParams
                .resource_name.filterMode.value,
          },
        })
        .then((res) => {
          console.log(res);
          this.items = res.data.executive_authorities;
          this.statistic = res.data.statistic;
        })
        .catch((err) => {
          appConfigStore.catchRequestError(err);
        })
        .finally(() => {
          this.data.isItemsLoading = false;
          this.data.isReceivingItemsLoading = false;
        });
    },

    receive() {
      this.items = [];
      this.data.isItemsLoading = true;
      this.data.isReceivingItemsLoading = true;
      console.log("received items");
      api
        .get(`/${sectionName}/receive`)
        .then(() => {
          this.read();
        })
        .catch((err) => {
          appConfigStore.catchRequestError(err);
          this.data.isItemsLoading = false;
          this.data.isReceivingItemsLoading = false;
        })
        .finally(() => {});
    },

    sendMail(mailType) {
      this.dialogs.email.isConfirmationLoading = true;
      this.dialogs.email.isConfirmationShown = false;
      api
        .get(`/${sectionName}/send_mail?mode=${mailType}`)
        .then((res) => {
          this.emailReport = res.data;
          this.dialogs.email.isReportShown = true;
        })
        .catch((err) => {
          appConfigStore.catchRequestError(err);
        })
        .finally(() => {
          this.dialogs.email.isConfirmationLoading = false;
        });
    },

    getReport() {
      this.dialogs.report.isConfirmationLoading = true;
      this.dialogs.email.isConfirmationShown = false;
      api
        .get(`/${sectionName}/get_report`, {
          responseType: "blob",
        })
        .then((res) => {
          // create file link in browser's memory
          const href = URL.createObjectURL(res.data);

          // create "a" HTML element with href to file & click
          const link = document.createElement("a");
          link.href = href;
          link.setAttribute("download", "report_datasets.xlsx");
          document.body.appendChild(link);
          link.click();

          // clean up "a" element & remove ObjectURL
          document.body.removeChild(link);
          URL.revokeObjectURL(href);
        })
        .catch((err) => {
          appConfigStore.catchRequestError(err);
        })
        .finally(() => {
          this.dialogs.report.isConfirmationLoading = false;
        });
    },
  },
});

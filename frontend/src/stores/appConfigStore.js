import { defineStore } from "pinia";

export const useAppConfigStore = defineStore("appConfig", {
  state: () => ({
    appURL: "https://data.lutskrada.gov.ua",
    filters: {
      data: {
        datasets: {
          selectedParams: {
            mode: "all",
            executive_authority_name: {
              value: "",
              filterMode: {
                label: "Містить",
                value: "include",
                shortName: "LIKE",
                type: "universal",
              },
            },
            dataset_title: {
              value: "",
              filterMode: {
                label: "Містить",
                value: "include",
                shortName: "LIKE",
                type: "universal",
              },
            },
            resource_name: {
              value: "",
              filterMode: {
                label: "Містить",
                value: "include",
                shortName: "LIKE",
                type: "universal",
              },
            },
          },
        },
      },
      availableParams: {
        items: [
          {
            label: "Містить",
            value: "include",
            shortName: "LIKE",
            type: "universal",
          },
          {
            label: "Не містить",
            value: "exclude",
            shortName: "EXCL",
            type: "universal",
          },
          { label: "Більше", value: "more", shortName: "MORE", type: "number" },
          { label: "Менше", value: "less", shortName: "LESS", type: "number" },
          {
            label: "Дорівнює",
            value: "equal",
            shortName: "EQL",
            type: "universal",
          },
          {
            label: "Не дорівнює",
            value: "notequal",
            shortName: "NEQL",
            type: "universal",
          },
          //...
        ],
      },
    },
  }),
  getters: {},
  actions: {
    catchRequestError(err) {},
  },
});

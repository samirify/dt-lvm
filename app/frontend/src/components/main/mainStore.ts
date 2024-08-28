import { defineStore } from "pinia";

export type AppErrorType = {
  title: string
  message?: string
}

export interface MainStoreState {
  isLoading: boolean
  appIsInitialised: boolean
  appError: AppErrorType
  longOperation: any
  serverTimezone?: string
}

const initialState: MainStoreState = {
  isLoading: true,
  appIsInitialised: false,
  appError: {
    title: ''
  },
  longOperation: {},
  serverTimezone: "",
};

export const useMainStore = defineStore({
  id: 'main',
  state: () => (initialState),
  actions: {
    setAppError(value: AppErrorType) {
      this.appError = value
    },
    setAppIsInitialised(value: boolean) {
      this.appIsInitialised = value
    },
    setLongOperation(value: any) {
      this.longOperation = value
    },
    setServerTimezone(value: string) {
      this.serverTimezone = value
    },
  }
})

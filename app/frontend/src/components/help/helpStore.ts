import { defineStore } from "pinia";

export type HelpTopic = {
  code: string
  title: string
  subTitle?: string
  contentFile: string
}

export type HelpSingleFAQ = {
  question: string
  answer: string
}

export interface HelpStoreState {
  availableTopics: HelpTopic[]
  availableFAQs?: HelpSingleFAQ[]
  openTopic: HelpTopic | null
}

const initialState: HelpStoreState = {
  availableTopics: [],
  availableFAQs: [],
  openTopic: null,
};

export const useHelpStore = defineStore({
  id: 'help',
  state: () => (initialState),
  actions: {
    setAvailableTopics(value: any) {
      this.availableTopics = value
    },
    setAvailableFAQs(value: any) {
      this.availableFAQs = value
    },
    setOpenTopic(value: HelpTopic) {
      this.openTopic = value
    },
    clearTopic() {
      this.openTopic = null;
    },
  }
})

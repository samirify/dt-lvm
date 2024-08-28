import { defineStore } from "pinia";
import type { DeploymentSocketMessage } from "../deployment/Deployment";


export type FontSize = {
  [label: string]: string;
};

export type FontSizes = {
  [key: string]: FontSize;
};

export const fontSizes: FontSizes = {
  "extra-small": {
    label: "Extra Small",
  },
  small: {
    label: "Small",
  },
  medium: {
    label: "Medium",
  },
  large: {
    label: "Large",
  },
  "extra-large": {
    label: "Extra Large",
  },
};

interface CodeViewerStoreState {
  codeLines: DeploymentSocketMessage[],
  coloured: string,
  showIcons: string,
  fontSize: string,
}

const initialState: CodeViewerStoreState = {
  codeLines: [],
  coloured: localStorage.getItem('coloured') || 'yes',
  showIcons: localStorage.getItem('showIcons') || 'yes',
  fontSize: localStorage.getItem('fontSize') || 'medium',
};

export const useCodeViewerStore = defineStore({
  id: 'codeViewer',
  state: () => (initialState),
  actions: {
    addCodeLines(value: DeploymentSocketMessage | DeploymentSocketMessage[]) {
      if (Array.isArray(value)) {
        for (const i in value) {
          this.codeLines.push(value[i]);
        }
      } else {
        this.codeLines.push(value);
      }
    },
    clearConsole() {
      this.codeLines = [];
    },
    toggleColor(value: string) {
      localStorage.setItem('coloured', value)
      this.coloured = value;
    },
    toggleIcons(value: string) {
      localStorage.setItem('coloured', value)
      this.showIcons = value;
    },
    setFontSize(value: string) {
      localStorage.setItem('fontSize', value)
      this.fontSize = value;
    },
  }
})
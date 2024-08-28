<script lang="ts" setup>
import {
  faCheckCircle,
  faClock,
  faTimesCircle,
} from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import dayjs from "dayjs";
import { RouterLink } from "vue-router";
import type { DeploymentPipeline } from "../../Deployment.d";
import { computed, reactive } from "vue";

const state = reactive<{
  searchQuery: string
  currentPageNumber: number
  rowsPerPage: number
}>({
  searchQuery: '',
  currentPageNumber: 1,
  rowsPerPage: 5,
})

const props = withDefaults(defineProps<{
  deployments: DeploymentPipeline[],
  title?: string
}>(), {
  deployments: () => []
})

const setRowsPerPage = (value: any) => {
  state.rowsPerPage = parseInt(value)
}

const goOnPrevPage = () => {
  if (state.currentPageNumber <= 1) {
    return;
  }

  state.currentPageNumber--;
};

const goOnNextPage = () => {
  if (state.currentPageNumber > props.deployments.length / state.rowsPerPage) {
    return;
  }

  state.currentPageNumber++;
};

const tableData = computed(() => {
  const start = (state.currentPageNumber - 1) * state.rowsPerPage;
  const end = state.currentPageNumber * state.rowsPerPage;

  if (state.searchQuery) {
    const _deployments = props.deployments.slice(start, end);
    return _deployments.filter((rec: any) =>
      Object.values(rec).some((val: any) => {
        return val.toString().includes(state.searchQuery);
      })
    );
  } else {
    return props.deployments.slice(start, end);
  }
});

const disablePrevBtn = computed(() => {
  if (state.currentPageNumber <= 1) return true;
  else return false;
})

const disableNextBtn = computed(() => {
  if (state.currentPageNumber > props.deployments.length / state.rowsPerPage) return true;
  else return false;
})

const numOfPages = computed(() => {
  return Math.ceil(props.deployments.length / state.rowsPerPage);
})
</script>

<template>
  <div class="row border-bottom bg-light mb-3 mx-1">
    <div class="col-sm-8">
      <h5 v-if="title" class="py-3 text-primary">{{ title }}</h5>
    </div>
    <div class="col-sm-4 py-3">
      <div class="input-group input-group-sm">
        <input placeholder="Search.." aria-label="Search" aria-describedby="search-addon" class="bg-white form-control"
          v-model="state.searchQuery" />

        <select class="form-select" style="max-width: 30%;" v-model="state.rowsPerPage"
          @change="(e: any) => setRowsPerPage(e.target.value)">
          <option value="5"> 5 </option>
          <option value="10"> 10 </option>
          <option value="25"> 25 </option>
          <option value="100"> 100 </option>
        </select>
      </div>
    </div>
  </div>

  <div className="overflow-auto">
    <table class="table table-responsive small shadow-sm">
      <thead>
        <tr>
          <th scope="col">Pipeline ID</th>
          <th scope="col">Project</th>
          <th scope="col">Branch</th>
          <th scope="col">Deployed At</th>
          <th scope="col" class="text-center">
            Status
          </th>
          <th scope="col" class="text-center">
            View
          </th>
        </tr>
      </thead>
      <tbody>
        <template v-if="tableData.length > 0">
          <tr v-for="deployment in tableData" :key="deployment.id">
            <td>{{ deployment.id }}</td>
            <td>{{ deployment.project_code }}</td>
            <td>{{ deployment.branch }}</td>
            <td>
              {{ deployment.created_at !== null
                ? dayjs(deployment.created_at).format(
                  "DD-MM-YYYY @ HH:mm:ss"
                )
                : "" }}
            </td>

            <td v-if="deployment.status_code === 'STARTED'" class="text-center">
              <FontAwesomeIcon class="text-warning" :icon="faClock" :spin="true" />
            </td>

            <td v-else-if="deployment.status_code === 'SUCCEEDED'" class="text-center">
              <FontAwesomeIcon class="text-success" :icon="faCheckCircle" />
            </td>
            <td v-else-if="deployment.status_code === 'FAILED'" class="text-center">
              <FontAwesomeIcon class="text-danger" :icon="faTimesCircle" :spin="true" />
            </td>
            <td v-else class="text-center">

            </td>

            <td class="text-center">
              <RouterLink class="text-decoration-none" :to="`/deployment-status/${deployment.id}`">
                View
              </RouterLink>
            </td>
          </tr>
        </template>

        <tr v-else>
          <td colspan="6" class="text-center p-4">
            No pipeline found!
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <ul class="justify-content-center pagination pagination-sm">
    <li :class="['page-item', disablePrevBtn ? 'disabled' : '']" :disabled="disablePrevBtn">
      <span v-if="disablePrevBtn" class="page-link">Previous</span>
      <a v-else class="page-link" role="button" tabindex="0" href="#" @click="goOnPrevPage">Previous</a>
    </li>
    <template v-for="page in numOfPages" :key="page">
      <li v-if="page === state.currentPageNumber" class="page-item active">
        <span class="page-link">{{ page }}<span class="visually-hidden">(current)</span></span>
      </li>
      <li v-else class="page-item"><a class="page-link" role="button" tabindex="0" href="#"
          @click="() => state.currentPageNumber = page">{{ page }}</a></li>
    </template>
    <li :class="['page-item', disableNextBtn ? 'disabled' : '']" :disabled="disableNextBtn">
      <span v-if="disableNextBtn" class="page-link">Next</span>
      <a v-else class="page-link" role="button" tabindex="0" href="#" @click="goOnNextPage">Next</a>
    </li>
  </ul>
</template>
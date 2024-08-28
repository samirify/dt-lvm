import axios from 'axios'

export default axios.create({
    baseURL: import.meta.env.VITE_DEPLOYMENT_API_URL_ROOT,
})

export const axiosPrivate = axios.create({
    baseURL: import.meta.env.VITE_DEPLOYMENT_API_URL_ROOT,
    headers: { 'Content-Type': 'application/json' },
    withCredentials: true,
})

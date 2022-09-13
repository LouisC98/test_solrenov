import { createApp } from "vue";
import axios from "axios";

createApp({
    compilerOptions: {
        delimiters: ["${", "}$"]
    },
    data() {
        return {
            name: 'Louis',
            categories: null,
            photos: null
        }
    },
    mounted() {
        axios.get('/galerie/all')
        .then((res) => {
            this.photos = JSON.parse(res.data)
        }); 
        axios.get('/categories')
        .then((res) => {
            this.categories = JSON.parse(res.data)
        }); 
    },
    methods: {
        searchByCategories(categoryId) {
            axios.get(`/galerie/${categoryId}`)
            .then((res) => {
                this.photos = JSON.parse(res.data)
            })
        },
        findAll() {
            axios.get('/galerie/all')
            .then((res) => {
            this.photos = JSON.parse(res.data)
            });
        }
    }
}).mount('#search')
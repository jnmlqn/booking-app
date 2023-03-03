import axios from 'axios';

const baseUrl = 'http://localhost:8000/api/v1/';

export class ApiService
{
	get(endpoint) {
		return axios.get(baseUrl + endpoint, this.getConfig());
	}

	post(endpoint, data) {
		return axios.post(baseUrl + endpoint, data, this.getConfig());
	}

	put(endpoint, data) {
		return axios.put(baseUrl + endpoint, data, this.getConfig());
	}

	delete(endpoint) {
		return axios.delete(baseUrl + endpoint, this.getConfig());
	}

	getConfig() {
		let cookie = {};

		document.cookie.split(';')
		.forEach(function(el) {
			let [key,value] = el.split('=');
			cookie[key.trim()] = value;
		});

		let config = {}

		if (cookie['accessToken'] !== null && cookie['accessToken'] !== undefined && cookie['accessToken'] !== '') {
			config = {
				headers: {
					Authorization: 'Bearer ' + cookie['accessToken']
				}
			}
		}

		return config;
	}
}
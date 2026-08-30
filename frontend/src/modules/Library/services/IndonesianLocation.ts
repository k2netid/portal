/**
 * Service to fetch geographical data for Indonesia.
 * Uses public API: https://www.emsifa.com/api-wilayah-indonesia/
 */

const BASE_URL = 'https://www.emsifa.com/api-wilayah-indonesia/api';

export interface LocationItem {
  id: string;
  name: string;
}

export const IndonesianLocation = {
  async getProvinces(): Promise<LocationItem[]> {
    try {
      const response = await fetch(`${BASE_URL}/provinces.json`);
      return await response.json();
    } catch (error) {
      console.error('Error fetching provinces:', error);
      return [];
    }
  },

  async getRegencies(provinceId: string): Promise<LocationItem[]> {
    try {
      const response = await fetch(`${BASE_URL}/regencies/${provinceId}.json`);
      return await response.json();
    } catch (error) {
      console.error('Error fetching regencies:', error);
      return [];
    }
  },

  async getDistricts(regencyId: string): Promise<LocationItem[]> {
    try {
      const response = await fetch(`${BASE_URL}/districts/${regencyId}.json`);
      return await response.json();
    } catch (error) {
      console.error('Error fetching districts:', error);
      return [];
    }
  },

  async getVillages(districtId: string): Promise<LocationItem[]> {
    try {
      const response = await fetch(`${BASE_URL}/villages/${districtId}.json`);
      return await response.json();
    } catch (error) {
      console.error('Error fetching villages:', error);
      return [];
    }
  }
};

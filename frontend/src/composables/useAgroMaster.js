import { ref, watch } from 'vue';

const defaultCrops = ['Mpunga', 'Mahindi', 'Maharagwe', 'Ufuta', 'Alizeti', 'Kahawa', 'Ngano', 'Karanga'];

const defaultUnits = [
  { name: 'Kilo (Kg)', kg: 1 },
  { name: 'Gunia (Bag)', kg: 100 },
  { name: 'Kiloba / Roba', kg: 50 },
  { name: 'Tani (Ton)', kg: 1000 },
  { name: 'Sado', kg: 4 },
  { name: 'Debbe', kg: 20 },
  { name: 'Pishi', kg: 2 },
  { name: 'Lumbesa', kg: 150 }
];

const loadSavedCrops = () => {
  try {
    const saved = localStorage.getItem('agroCropsList');
    if (saved) {
      const parsed = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length > 0) return parsed;
    }
  } catch (e) {}
  return defaultCrops;
};

const loadSavedUnits = () => {
  try {
    const saved = localStorage.getItem('agroUnitsList');
    if (saved) {
      const parsed = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length > 0) return parsed;
    }
  } catch (e) {}
  return defaultUnits;
};

const cropsList = ref(loadSavedCrops());
const unitsList = ref(loadSavedUnits());

watch(cropsList, (newVal) => {
  localStorage.setItem('agroCropsList', JSON.stringify(newVal));
}, { deep: true });

watch(unitsList, (newVal) => {
  localStorage.setItem('agroUnitsList', JSON.stringify(newVal));
}, { deep: true });

export function useAgroMaster() {
  const addCrop = (cropName) => {
    const name = cropName.trim();
    if (!name) return false;
    if (cropsList.value.some(c => c.toLowerCase() === name.toLowerCase())) {
      return false;
    }
    cropsList.value.push(name);
    return true;
  };

  const deleteCrop = (cropName) => {
    cropsList.value = cropsList.value.filter(c => c !== cropName);
  };

  const addUnit = (unitName, kgRatio = 1, formulaText = '') => {
    const name = unitName.trim();
    if (!name) return false;
    if (unitsList.value.some(u => u.name.toLowerCase() === name.toLowerCase())) {
      return false;
    }
    unitsList.value.push({ name, kg: Number(kgRatio) || 1, formulaText: formulaText || '' });
    return true;
  };

  const updateUnitRatio = (unitName, newKgRatio, formulaText = '') => {
    const found = unitsList.value.find(u => u.name.toLowerCase() === unitName.toLowerCase());
    if (found) {
      found.kg = Number(newKgRatio) || 1;
      if (formulaText) {
        found.formulaText = formulaText;
      }
      return true;
    }
    return false;
  };

  const updateUnit = (oldName, newName, newKgRatio, formulaText = '') => {
    const found = unitsList.value.find(u => u.name.toLowerCase() === oldName.toLowerCase());
    if (found) {
      const trimmedNew = (newName || '').trim();
      if (trimmedNew && trimmedNew.toLowerCase() !== oldName.toLowerCase()) {
        const exists = unitsList.value.some(u => u.name.toLowerCase() === trimmedNew.toLowerCase());
        if (exists) return false;
        found.name = trimmedNew;
      }
      if (newKgRatio !== undefined && newKgRatio !== null && !isNaN(Number(newKgRatio))) {
        found.kg = Number(newKgRatio) || 1;
      }
      if (formulaText) {
        found.formulaText = formulaText;
      }
      return true;
    }
    return false;
  };

  const deleteUnit = (unitName) => {
    unitsList.value = unitsList.value.filter(u => u.name !== unitName);
  };

  const getUnitKg = (unitName, quantity = 1) => {
    if (!unitName) return Number(quantity) || 0;
    const norm = String(unitName).toLowerCase().trim();
    const found = unitsList.value.find(u => u.name.toLowerCase().includes(norm) || norm.includes(u.name.toLowerCase()));
    if (found) {
      return (Number(quantity) || 0) * (found.kg || 1);
    }
    if (norm.includes('tani') || norm.includes('ton')) return (Number(quantity) || 0) * 1000;
    if (norm.includes('gunia') || norm.includes('bag')) return (Number(quantity) || 0) * 100;
    if (norm.includes('roba') || norm.includes('kiloba')) return (Number(quantity) || 0) * 50;
    if (norm.includes('lumbesa')) return (Number(quantity) || 0) * 150;
    if (norm.includes('sado')) return (Number(quantity) || 0) * 4;
    if (norm.includes('debbe')) return (Number(quantity) || 0) * 20;
    if (norm.includes('pishi')) return (Number(quantity) || 0) * 2;
    return (Number(quantity) || 0) * 1;
  };

  // Google Currency Converter Style Universal Unit Converter
  const convertUnits = (amount, fromUnitName, toUnitName) => {
    const qty = Number(amount) || 0;
    if (!fromUnitName || !toUnitName) return qty;
    const fromKg = getUnitKg(fromUnitName, 1);
    const toKg = getUnitKg(toUnitName, 1);
    if (!toKg || toKg === 0) return 0;
    return (qty * fromKg) / toKg;
  };

  return {
    cropsList,
    unitsList,
    addCrop,
    deleteCrop,
    addUnit,
    updateUnitRatio,
    updateUnit,
    deleteUnit,
    getUnitKg,
    convertUnits
  };
}

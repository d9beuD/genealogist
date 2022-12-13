export default {
  dateNormalizer: (date: string) => {
    return new Date(date).toISOString();
  },

  calendar: (date: string) => new Date(date).toLocaleString(),
};

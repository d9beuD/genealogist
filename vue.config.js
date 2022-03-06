module.exports = {
  pluginOptions: {
    electronBuilder: {
      builderOptions: {
        appId: "com.d9beud.${name}",
        productName: "Genealogist",
        copyright: "Copyright © 2022 D9beuD",
        mac: {
          category: "public.app-category.utilities",
          target: "default",
          darkModeSupport: true,
        },
      },
    },
  },
};

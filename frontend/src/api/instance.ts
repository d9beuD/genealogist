export const baseURL = import.meta.env.DEV ? "http://localhost:8000/" : "";
const defaultConfig: RequestInit = {
  headers: {
    "Content-Type": "application/json",
  },
  credentials: "include",
};

const request = <T = any>(
  method: string,
  url: URL,
  config: RequestInit
): Promise<T> =>
  fetch(
    url,
    Object.assign<RequestInit, RequestInit, RequestInit>(
      { method: method },
      defaultConfig,
      config
    )
  ).then((response) => {
    if (!response.ok) {
      throw Error(`Fetch API error: ${response.text}`);
    }
    return response.json();
  });

const instance = {
  get: (
    resource: string,
    query: Record<string, string> = {},
    config: RequestInit = {}
  ) => {
    let url = new URL(resource, baseURL);
    const searchParams = new URLSearchParams();

    if (Object.entries(query).length > 0) {
      for (const key in query) {
        searchParams.append(key, query[key]);
      }

      url = new URL(`${url.toString()}?${searchParams.toString()}`);
    }

    return request("GET", url, config);
  },

  post: (resource: string, content: unknown, config: RequestInit = {}) => {
    const url = new URL(resource, baseURL);

    return request(
      "POST",
      url,
      Object.assign<RequestInit, RequestInit, RequestInit>(
        {},
        { body: JSON.stringify(content) },
        config
      )
    );
  },

  postMultipart: <T = any>(
    resource: string,
    file: { name: string; data: File },
    config: RequestInit = {}
  ): Promise<T> => {
    const url = new URL(resource, baseURL);
    const form = new FormData();
    form.append(file.name, file.data);

    return fetch(
      url,
      Object.assign<RequestInit, RequestInit>(
        { method: "POST", body: form, credentials: "include" },
        config
      )
    ).then((response) => {
      if (!response.ok) {
        throw Error(`Fetch API error: ${response.text}`);
      }
      return response.json();
    });
  },

  put: (resource: string, content: unknown, config: RequestInit = {}) => {
    const url = new URL(resource, baseURL);

    return request(
      "PUT",
      url,
      Object.assign<RequestInit, RequestInit, RequestInit>(
        {},
        { body: JSON.stringify(content) },
        config
      )
    );
  },

  delete: (resource: string, config: RequestInit = {}) => {
    const url = new URL(resource, baseURL);
    return request("DELETE", url, config);
  },
};

export default instance;

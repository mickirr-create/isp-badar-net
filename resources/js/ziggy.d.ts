declare function route(
    name?: string,
    params?: Record<string, string | number> | undefined,
    absolute?: boolean,
): string;

export interface User {
    id: number;
    name: string;
    email: string;
}

export interface StudentAccount {
    id: number;
    name: string;
    nisn: string;
}

export interface PageProps {
    name: string;
    auth: {
        user: User | null;
        student: StudentAccount | null;
    };
    flash: {
        success: string | null;
        error: string | null;
        import_errors: string[];
    };
    errors: Record<string, string>;
    [key: string]: unknown;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginated<T> {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

import { computed, onMounted, ref, watch } from 'vue';
import StorefrontController from '@/actions/App/Http/Controllers/StorefrontController';

// ────────────────── Types ──────────────────
export type Branch = {
    id: number;
    name: string;
    address: string;
    opening_time: string;
    closing_time: string;
};

export type Category = {
    id: number;
    name: string;
    description: string;
    branch: { id: number; name: string } | null;
};

export type Product = {
    id: number;
    code: string;
    name: string;
    description: string;
    unit_price: number;
    promotion_discount: number;
    final_price: number;
    stock: number;
    image: string | null;
    category: { id: number; name: string } | null;
    branch: { id: number; name: string } | null;
};

export type ProductsMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

export type PromotionItem = {
    id: number;
    name: string;
    image: string | null;
};

export type Promotion = {
    name_promotion: string;
    price: number;
    image: string | null;
    items_count: number;
    items: PromotionItem[];
};

export type CartEntry = {
    product: Product;
    quantity: number;
};

// ────────────────── Composable ──────────────────
export function useStorefront() {
    const branches = ref<Branch[]>([]);
    const categories = ref<Category[]>([]);
    const products = ref<Product[]>([]);
    const offers = ref<Product[]>([]);
    const promotions = ref<Promotion[]>([]);

    const selectedBranchId = ref<number | null>(null);
    const selectedCategoryId = ref<number | null>(null);
    const search = ref('');
    const currentPage = ref(1);
    const productsMeta = ref<ProductsMeta>({ current_page: 1, last_page: 1, per_page: 18, total: 0 });

    const loadingBranches = ref(false);
    const loadingCatalog = ref(false);
    const loadingPromotions = ref(false);
    const checkoutLoading = ref(false);
    const checkoutMessage = ref('');
    const checkoutError = ref('');

    const cart = ref<Record<number, CartEntry>>({});
    const paymentMethod = ref<'cash' | 'card' | 'yape' | 'plin'>('cash');
    const voucher = ref<'ticket' | 'invoice'>('ticket');
    const document = ref('');

    // Debounce timer
    let searchTimer: ReturnType<typeof setTimeout> | null = null;

    // Derived
    const filteredCategories = computed(() => {
        if (selectedBranchId.value === null) return categories.value;
        return categories.value.filter((c) => c.branch?.id === selectedBranchId.value);
    });

    const cartItems = computed(() => Object.values(cart.value));
    const cartTotal = computed(() =>
        cartItems.value.reduce((sum, row) => sum + row.quantity * Number(row.product.final_price), 0),
    );
    const cartCount = computed(() => cartItems.value.reduce((sum, row) => sum + row.quantity, 0));

    // ── CSRF helper ──
    const getCsrfToken = (): string =>
        (document as unknown as Document).querySelector?.('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    // ── Fetchers ──
    const fetchBranches = async () => {
        loadingBranches.value = true;
        try {
            const res = await fetch(StorefrontController.branches.url(), { headers: { Accept: 'application/json' } });
            const payload = await res.json();
            branches.value = payload.data ?? [];
        } finally {
            loadingBranches.value = false;
        }
    };

    const fetchCategories = async () => {
        const params: Record<string, string | number | boolean> = {};
        if (selectedBranchId.value !== null) params.branch_id = selectedBranchId.value;
        const res = await fetch(StorefrontController.categories.url({ query: params }), {
            headers: { Accept: 'application/json' },
        });
        const payload = await res.json();
        categories.value = payload.data ?? [];
    };

    const fetchProducts = async () => {
        loadingCatalog.value = true;
        try {
            const params: Record<string, string | number | boolean> = { per_page: 12, page: currentPage.value };
            if (selectedBranchId.value !== null) params.branch_id = selectedBranchId.value;
            if (selectedCategoryId.value !== null) params.category_id = selectedCategoryId.value;
            if (search.value.trim()) params.search = search.value.trim();
            const res = await fetch(StorefrontController.products.url({ query: params }), {
                headers: { Accept: 'application/json' },
            });
            const payload = await res.json();
            products.value = payload.data ?? [];
            productsMeta.value = payload.meta ?? { current_page: 1, last_page: 1, per_page: 12, total: 0 };
        } finally {
            loadingCatalog.value = false;
        }
    };

    const fetchOffers = async () => {
        const params: Record<string, string | number | boolean> = { per_page: 6, only_offers: true };
        if (selectedBranchId.value !== null) params.branch_id = selectedBranchId.value;
        if (selectedCategoryId.value !== null) params.category_id = selectedCategoryId.value;
        if (search.value.trim()) params.search = search.value.trim();
        const res = await fetch(StorefrontController.products.url({ query: params }), {
            headers: { Accept: 'application/json' },
        });
        const payload = await res.json();
        offers.value = payload.data ?? [];
    };

    const fetchPromotions = async () => {
        loadingPromotions.value = true;
        try {
            const params: Record<string, string | number | boolean> = {};
            if (selectedBranchId.value !== null) params.branch_id = selectedBranchId.value;
            const res = await fetch(StorefrontController.promotions.url({ query: params }), {
                headers: { Accept: 'application/json' },
            });
            const payload = await res.json();
            promotions.value = payload.data ?? [];
        } finally {
            loadingPromotions.value = false;
        }
    };

    const refreshStorefrontData = () => {
        checkoutError.value = '';
        return Promise.all([fetchCategories(), fetchProducts(), fetchOffers(), fetchPromotions()]);
    };

    // ── Cart ──
    const addToCart = (product: Product) => {
        const existing = cart.value[product.id];
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.value[product.id] = { product, quantity: 1 };
        }
    };

    const decreaseFromCart = (productId: number) => {
        const existing = cart.value[productId];
        if (!existing) return;
        if (existing.quantity <= 1) {
            delete cart.value[productId];
        } else {
            existing.quantity -= 1;
        }
    };

    const removeFromCart = (productId: number) => {
        delete cart.value[productId];
    };

    // ── Checkout ──
    const checkout = async () => {
        checkoutError.value = '';
        checkoutMessage.value = '';

        if (cartItems.value.length === 0) {
            checkoutError.value = 'Tu carrito está vacío.';
            return;
        }

        checkoutLoading.value = true;
        try {
            const response = await fetch(StorefrontController.checkout.url(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    branch_id: selectedBranchId.value,
                    items: cartItems.value.map((row) => ({
                        product_id: row.product.id,
                        quantity: row.quantity,
                    })),
                    payment_method: paymentMethod.value,
                    voucher: voucher.value,
                    document: document.value || null,
                }),
            });

            const result = await response.json();

            if (!response.ok) {
                checkoutError.value = result?.message ?? 'No se pudo completar la compra.';
                return;
            }

            checkoutMessage.value = `¡Compra registrada! Venta #${result?.data?.sale_id}.`;
            cart.value = {};
            await refreshStorefrontData();
        } catch {
            checkoutError.value = 'Error de red al registrar la compra.';
        } finally {
            checkoutLoading.value = false;
        }
    };

    // ── Filters ──
    const setBranch = (id: number | null) => {
        selectedBranchId.value = id;
        selectedCategoryId.value = null;
        currentPage.value = 1;
    };

    const setCategory = (id: number | null) => {
        selectedCategoryId.value = id;
        currentPage.value = 1;
    };

    const setSearch = (q: string) => {
        search.value = q;
    };

    const setPage = (page: number) => {
        currentPage.value = page;
    };

    // ── Watchers ──
    watch([selectedBranchId, selectedCategoryId], async () => {
        if (
            selectedCategoryId.value !== null &&
            !filteredCategories.value.some((c) => c.id === selectedCategoryId.value)
        ) {
            selectedCategoryId.value = null;
        }
        await refreshStorefrontData();
    });

    // Debounced search watcher
    watch(search, () => {
        if (searchTimer) clearTimeout(searchTimer);
        searchTimer = setTimeout(async () => {
            currentPage.value = 1;
            await Promise.all([fetchProducts(), fetchOffers()]);
        }, 400);
    });

    // Page watcher
    watch(currentPage, async () => {
        await fetchProducts();
    });

    onMounted(async () => {
        await fetchBranches();
        await refreshStorefrontData();
    });

    return {
        branches, categories, filteredCategories,
        products, offers, promotions,
        productsMeta,
        selectedBranchId, selectedCategoryId,
        search, currentPage,
        loadingBranches, loadingCatalog, loadingPromotions,
        checkoutLoading, checkoutMessage, checkoutError,
        cart, cartItems, cartTotal, cartCount,
        paymentMethod, voucher, document,
        setBranch, setCategory, setSearch, setPage,
        addToCart, decreaseFromCart, removeFromCart,
        checkout,
    };
}

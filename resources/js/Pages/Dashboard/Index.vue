<template>
    <div>
        <main id="main" class="main">
            <div class="pagetitle">
                <h1 class="theme-text-color">Dashboard</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="dashboard">TTC Global</a>
                        </li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
            </div>
            <!-- End Page Title -->

            <section class="section dashboard">
                <div class="row">
                    <!-- Filter -->

                    <div class="d-flex justify-content-end mb-3 mt-3">
                        <div class="col-md-3">
                            <Multiselect
                                v-model="selectedFilter"
                                :options="filterOptions"
                                :searchable="true"
                                @select="applyFilter"
                                placeholder="Filter By"
                            />
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-xxl-4 col-md-4">
                                <div class="card info-card bg-white">
                                    <div class="card-body">
                                        <h5 class="card-title theme-text-color">Cash In</h5>
                                        <div
                                            class="d-flex align-items-center justify-content-between"
                                        >
                                            <h6>
                                                {{ formatCurrency(cashIn) }}
                                            </h6>
                                            <span
                                                class="icon-bubble bg-success"
                                            >
                                                <i
                                                    class="bi bi-cash text-white"
                                                ></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xxl-4 col-md-4">
                                <div class="card info-card bg-white">
                                    <div class="card-body">
                                        <h5 class="card-title theme-text-color">Cash Out</h5>
                                        <div
                                            class="d-flex align-items-center justify-content-between"
                                        >
                                            <h6 class="text-danger">
                                                {{ formatCurrency(cashOut) }}
                                            </h6>
                                            <span class="icon-bubble bg-danger">
                                                <i
                                                    class="bi bi-cash-coin text-white"
                                                ></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xxl-4 col-md-4">
                                <div class="card info-card bg-white">
                                    <div class="card-body">
                                        <h5 class="card-title theme-text-color">Balance</h5>
                                        <div
                                            class="d-flex align-items-center justify-content-between"
                                        >
                                            <h6
                                                :class="{
                                                    'text-success':
                                                        balance >= 0,
                                                    'text-danger': balance < 0,
                                                }"
                                            >
                                                {{ formatCurrency(balance) }}
                                            </h6>
                                            <span
                                                :class="
                                                    balance >= 0
                                                        ? 'icon-bubble bg-success'
                                                        : 'icon-bubble bg-danger'
                                                "
                                            >
                                                <i
                                                    class="bi bi-bank text-white"
                                                ></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts -->
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    Income vs Expense (Bar Chart - Selected
                                    Range)
                                </h5>
                                <canvas id="barChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<script>
import Master from "../Layout/Master.vue";
import { Chart, registerables } from "chart.js";
import Multiselect from "@vueform/multiselect";
import axios from "axios";

Chart.register(...registerables);

export default {
    layout: Master,
    components: {
        Multiselect,
    },
    data() {
        return {
            transactionEntries: [],
            incomeTypeDetails: [],
            incomeTypeTotal: 0,

            expenseTypeTotal: 0,

            expenseDetails: [],
            cashIn: 0,
            cashOut: 0,
            balance: 0,
            last6MonthsData: [],
            last12MonthsData: [],
            selectedFilter: "3", // Default to last 3 months
            titleCashIn: "Current Month Cash In",
            titleCashIn: "Current Month Cash Out",
            titleCashIn: "Last 3 Months Cash In",
            titleCashOut: "Last 3 Months Cash Out",
            titleBalance: "Last 3 Months Balance",
            filterLabel: "Last 3 Months",
            filterOptions: [
                { value: "1", label: "Current Month" },
                { value: "3", label: "Last 3 Months" },
                { value: "6", label: "Last 6 Months" },
                { value: "12", label: "Last 1 Year" },
                // { value: "all", label: "Overall" },
            ],
        };
    },
    methods: {
        formatCurrency(value) {
            return new Intl.NumberFormat("en-PK", {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            }).format(value);
        },

        applyFilter() {
            if (this.selectedFilter === "all") {
                // Set filter label and titles for overall data
                this.filterLabel = "Overall";
                this.titleCashIn = "Overall Cash In";
                this.titleCashOut = "Overall Cash Out";
                this.titleBalance = "Overall Balance";

                // Pass all entries to calculateStats
                this.calculateStats(this.transactionEntries);
            } else {
                // Handle month-based filtering
                const filterMonths = parseInt(this.selectedFilter);
                const currentDate = new Date();

                const filteredData = this.transactionEntries.filter((entry) => {
                    const entryDate = new Date(entry.transaction_date);
                    const monthDifference =
                        (currentDate.getFullYear() - entryDate.getFullYear()) *
                            12 +
                        currentDate.getMonth() -
                        entryDate.getMonth();
                    return monthDifference < filterMonths;
                });

                this.filterLabel = `Last ${this.selectedFilter} Months`;
                this.titleCashIn = `${this.filterLabel} Cash In`;
                this.titleCashOut = `${this.filterLabel} Cash Out`;
                this.titleBalance = `${this.filterLabel} Balance`;

                this.calculateStats(filteredData);
            }

            this.updateCharts();
        },
        updateCharts() {
            // Check and destroy existing charts
            if (this.barChart) {
                this.barChart.destroy();
                this.barChart = null;
            }
            // if (this.lineChart) {
            //     this.lineChart.destroy();
            //     this.lineChart = null;
            // }

            if (this.selectedFilter === "all") {
                // Aggregate totals for cash in and cash out
                const totalCashIn = this.transactionEntries.reduce(
                    (sum, entry) => sum + parseFloat(entry.cash_in || 0),
                    0
                );

                const totalCashOut = this.transactionEntries.reduce(
                    (sum, entry) => sum + parseFloat(entry.cash_out || 0),
                    0
                );

                // Bar Chart (Overall Data for All)
                const barCtx = document
                    .getElementById("barChart")
                    .getContext("2d");
                this.barChart = new Chart(barCtx, {
                    type: "bar",
                    data: {
                        labels: ["Cash In", "Cash Out"], // Overall categories
                        datasets: [
                            {
                                label: "Overall Data",
                                data: [totalCashIn, totalCashOut],
                                backgroundColor: ["#4caf50", "#f44336"], // Green for Cash In, Red for Cash Out
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) =>
                                        `${
                                            context.label
                                        }: ${this.formatCurrency(context.raw)}`,
                                },
                            },
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: "Transaction Type",
                                },
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: "Amount",
                                },
                                beginAtZero: true,
                            },
                        },
                    },
                });
            } else {
                // Existing logic for monthly filters
                const barCtx = document
                    .getElementById("barChart")
                    .getContext("2d");
                this.barChart = new Chart(barCtx, {
                    type: "bar",
                    data: {
                        labels: this.last6MonthsData.map((item) => item.label),
                        datasets: [
                            {
                                label: "Income",
                                data: this.last6MonthsData.map(
                                    (item) => item.income
                                ),
                                backgroundColor: "#4caf50",
                            },
                            {
                                label: "Expense",
                                data: this.last6MonthsData.map(
                                    (item) => item.expense
                                ),
                                backgroundColor: "#f44336",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: "top",
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) =>
                                        `${
                                            context.dataset.label
                                        }: ${this.formatCurrency(context.raw)}`,
                                },
                            },
                        },
                    },
                });
            }
        },

        fetchTransactionEntries() {
    axios
        .get(route("api.dashboard.transaction.fetch"))
        .then((response) => {
            const visaData = response.data.visas || [];
            const familyVisaData = response.data.family_visas || [];

            this.transactionEntries = [
                ...visaData.map(v => ({
                    transaction_date: v.date,
                    cash_in: parseFloat(v.amount) || 0,
                    cash_out: parseFloat(v.visa_fee) || 0
                })),
                ...familyVisaData.map(fv => ({
                    transaction_date: fv.date,
                    cash_in: parseFloat(fv.amount) || 0,
                    cash_out: parseFloat(fv.visa_fee) || 0
                }))
            ];

            this.applyFilter();
        })
        .catch((error) => {
            console.error("Error fetching visa transactions:", error);
        });
}
,

        calculateStats() {
            const currentDate = new Date();

            // Filter transactions based on selected time range
            const filteredEntries = this.transactionEntries.filter((entry) => {
                const entryDate = new Date(entry.transaction_date);

                if (isNaN(entryDate)) return false; // Skip invalid dates

                if (this.selectedFilter === "all") return true;

                const filterMonths = parseInt(this.selectedFilter, 10);
                const pastDate = new Date();
                pastDate.setMonth(currentDate.getMonth() - filterMonths);
                pastDate.setDate(1); // Ensuring we start from the first of the month

                return entryDate >= pastDate;
            });

            // Calculate total cash in and out
            this.cashIn = filteredEntries.reduce(
                (sum, entry) => sum + (parseFloat(entry.cash_in) || 0),
                0
            );
            this.cashOut = filteredEntries.reduce(
                (sum, entry) => sum + (parseFloat(entry.cash_out) || 0),
                0
            );

            this.balance = this.cashIn - this.cashOut;

            // Ensure separate calculations for 6-month and 12-month data
            this.last6MonthsData = this.groupDataByMonths(filteredEntries, 6);
            this.last12MonthsData = this.groupDataByMonths(filteredEntries, 12);
        },

        applyFilter() {
            const filterMonths =
                this.selectedFilter === "all"
                    ? Infinity
                    : parseInt(this.selectedFilter);
            const currentDate = new Date();

            const filteredData = this.transactionEntries.filter((entry) => {
                const entryDate = new Date(entry.transaction_date);
                const monthDifference =
                    (currentDate.getFullYear() - entryDate.getFullYear()) * 12 +
                    currentDate.getMonth() -
                    entryDate.getMonth();
                return monthDifference < filterMonths;
            });

            this.calculateStats(filteredData);
            this.updateCharts();
        },

        groupDataByMonths(entries, months) {
            const groupedData = [];
            const currentDate = new Date();

            const formatMonth = new Intl.DateTimeFormat("en-US", {
                month: "short", // Abbreviated month name (e.g., Jan, Feb)
                year: "numeric", // Full year (e.g., 2025)
            });

            for (let i = 0; i < months; i++) {
                const month = new Date();
                month.setMonth(currentDate.getMonth() - i);
                const monthLabel = formatMonth.format(month);

                groupedData.push({
                    label: monthLabel,
                    income: 0,
                    expense: 0,
                });
            }

            entries.forEach((entry) => {
                const entryDate = new Date(entry.transaction_date);
                const monthLabel = formatMonth.format(entryDate);

                const groupIndex = groupedData.findIndex(
                    (item) => item.label === monthLabel
                );

                if (groupIndex !== -1) {
                    groupedData[groupIndex].income +=
                        parseFloat(entry.cash_in) || 0;
                    groupedData[groupIndex].expense +=
                        parseFloat(entry.cash_out) || 0;
                }
            });

            return groupedData;
        },
    },
    mounted() {
        this.fetchTransactionEntries();
    },
    watch: {
        transactionEntries() {
            this.applyFilter();
        },
        selectedFilter() {
            this.applyFilter();
        },
    },
};
</script>

<style scoped>
@import "@vueform/multiselect/themes/default.css";
.info-card {
    background: #f8f9fa;
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    padding: 20px;
}

.icon-bubble {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
canvas {
    width: 100% !important;
    height: 400px !important;
}
</style>

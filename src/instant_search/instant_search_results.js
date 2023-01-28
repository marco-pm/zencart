import React, { useState, useEffect, useRef } from 'react';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider, useQuery } from "@tanstack/react-query";
import parse from 'html-react-parser';
import {useDebounce} from "use-debounce";
import {SlideDown} from "react-slidedown";

const queryClient = new QueryClient();

async function fetchResults(queryText, resultPage, alphaFilterId, sort) {
    const data = new FormData();
    data.append('keyword', queryText);
    data.append('scope', 'page');
    data.append('resultPage', resultPage);
    data.append('alpha_filter_id', alphaFilterId);
    data.append('sort', sort);
    data.append('securityToken', instantSearchResultSecurityToken);

    const response = await fetch('ajax.php?act=ajaxInstantSearch&method=instantSearch', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: data
    });
    return await response.json();
}

function ResultsContainer({ queryText, initialResultPage, alphaFilterId, sort }) {
    const [resultPage, setResultPage] = useState(initialResultPage);
    const [previousData, setPreviousData] = useState(null);
    const endResultsRef = useRef(null);

    const {isLoading, isError, data, error} = useQuery({
        queryKey: ['results', queryText, resultPage, alphaFilterId, sort],
        queryFn: async () => fetchResults(queryText, resultPage, alphaFilterId, sort).then(data => JSON.parse(data)),
    });

    useEffect(() => {
        // If the HTML response is empty or the number of products is the same as before,
        // we have reached the end of results
        const isLastPage = !data ||
            (data.count && data.count === 0) ||
            (previousData && previousData.count && previousData.count === data.count);

        if (data) {
            setPreviousData(data);
        }

        if (endResultsRef.current !== null && !isLastPage) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        // Load more results
                        setResultPage(parseInt(resultPage) + 1);
                    }
                });
            });

            observer.observe(endResultsRef.current);

            return () => {
                observer.disconnect();
            };
        }
    }, [data]);

    const instantSearchFilterDivSelector = '.instantSearchResults__sorterRow';

    if (isLoading) {
        return (
            <>
                {previousData && previousData.results ? parse(previousData.results) : ''}
                <div id="instantSearchResults__loadingWrapper">
                    {loadingResultsText}
                    <div className="spinner"></div>
                </div>
            </>
        )
    }

    if (isError) {
        console.log(error);
        return <></>;
    }

    if (!data || !data.results || data.count === 0) {
        document.querySelector(instantSearchFilterDivSelector).style.display = 'none';
        return (
            <div id="instantSearchResults__noResultsFoundWrapper">
                {noProductsFoundText}
            </div>
        )
    }

    document.querySelector(instantSearchFilterDivSelector).style.display = 'block';

    // Update URL page parameter (if we are not past the last page)
    if (previousData && previousData.count && previousData.count !== data.count) {
        const url = new URL(window.document.URL);
        url.searchParams.set('page', resultPage);
        window.history.replaceState(null, '', url.toString());
    }

    return  (
        <>
            {parse(data.results)}
            <div ref={endResultsRef} aria-hidden="true" />
        </>
    )
}

function InstantSearchResults() {
    const params         = new URLSearchParams(window.location.search)
    const keyword        = params.get('keyword') ?? '';
    const page           = params.get('page') ?? 1;
    const alphaFilterId  = params.get('alpha_filter_id') ?? '';
    const sort           = params.get('sort') ?? '20a';

    return (
        <React.StrictMode>
            <QueryClientProvider client={queryClient}>
                <ResultsContainer queryText={keyword} initialResultPage={page} alphaFilterId={alphaFilterId} sort={sort} />
            </QueryClientProvider>
        </React.StrictMode>
    )
}

const container = document.querySelector('#productListing');
const root = createRoot(container);
root.render(<InstantSearchResults />);

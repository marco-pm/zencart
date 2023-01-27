import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import { useDebounce } from 'use-debounce';
import { QueryClient, QueryClientProvider, useQuery } from "@tanstack/react-query";
import parse from 'html-react-parser';
import { SlideDown } from 'react-slidedown';

async function fetchResults(queryTextParsed) {
    const data = new FormData();
    data.append('keyword', queryTextParsed);
    data.append('scope', 'dropdown');
    data.append('securityToken', instantSearchSecurityToken);

    const response = await fetch('ajax.php?act=ajaxInstantSearch&method=instantSearch', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: data
    });
    return await response.json();
}

function ResultsContainer({ queryTextParsed, containerIndex }) {
    const {isLoading, isError, data, error} = useQuery({
        queryKey: ['results', queryTextParsed],
        queryFn: async () => fetchResults(queryTextParsed).then(data => JSON.parse(data)),
    });
    const [previousData, setPreviousData] = useState(null);
    const [isSlideDownRendered, setIsSlideDownRendered] = useState(false);
    const [additionalClass, setAdditionalClass] = useState('');

    const resultsContainerSelector = 'instantSearchResultsDropdownContainer';
    const resultsContainerId = `${resultsContainerSelector}-${containerIndex}`;

    useEffect(() => {
        if (data) {
            setPreviousData(data);
        }
    }, [data]);

    useEffect(() => {
        const div = document.querySelector(`#${resultsContainerId}`);
        if (div) {
            if (div.clientWidth > 250) {
                setAdditionalClass(' instantSearchResultsDropdownContainer--lg');
            } else {
                setAdditionalClass('');
            }
        }
    }, [data]);

    if (isLoading) {
        if (previousData && previousData.results) {
            if (!isSlideDownRendered) {
                setIsSlideDownRendered(true);
            }
            return (
                <div id={resultsContainerId} className={`${resultsContainerSelector}${additionalClass}`}>
                    {parse(previousData.results)}
                </div>
            );
        } else {
            if (isSlideDownRendered) {
                setIsSlideDownRendered(false);
            }
            return <></>;
        }
    }

    if (isError) {
        console.log(error);
        return <></>;
    }

    if (!data || !data.results) {
        return <></>;
    }

    let results;
    if (!isSlideDownRendered) {
        results = <SlideDown>{parse(data.results)}</SlideDown>;
    } else {
        results = parse(data.results);
    }

    return (
        <div id={resultsContainerId} className={`${resultsContainerSelector}${additionalClass}`}>
            {results}
        </div>
    );
}

function InstantSearchDropdown({ inputTextAttributes, containerIndex }) {
    const [queryText, setQueryText] = useState('');
    const [debouncedQueryText] = useDebounce(queryText, instantSearchDropdownInputWaitTime);
    const [showResults, setShowResults] = useState(false);

    const queryClient = new QueryClient();

    const queryTextParsed = debouncedQueryText.replace(/^\s+/, "").replace(/  +/g, ' ');

    function handleInput() {
        return e => {
            queryClient.cancelQueries({queryKey: ['results']});
            setQueryText(e.target.value);
        };
    }

    return (
        <React.StrictMode>
            <QueryClientProvider client={queryClient}>
                <input
                    type="text"
                    value={queryText}
                    onInput={handleInput()}
                    onFocus={() => setShowResults(true)}
                    /*onBlur={() => setShowResults(false)}*/
                    {...inputTextAttributes}
                />
                {
                    showResults &&
                    queryTextParsed.length >= instantSearchDropdownInputMinLength &&
                    <ResultsContainer queryTextParsed={debouncedQueryText} containerIndex={containerIndex} />
                }
            </QueryClientProvider>
        </React.StrictMode>
    )
}

// Add autocomplete dropdown on search inputs
const instantSearchInputs = document.querySelectorAll(instantSearchDropdownInputSelector);

for (let i = 0; i < instantSearchInputs.length; i++) {
    const input = instantSearchInputs[i];

    const inputTextAttributes = {
        name: input.name,
        className: input.class,
        size: input.size,
        maxLength: input.maxLength,
        placeholder: input.placeholder,
        'aria-label': input.getAttribute('aria-label'),
    };

    // Trasform inline style to JSX format
    inputTextAttributes.style = {};
    for (let i = 0; i < input.style.length; i++) {
        const styleName = input.style[i];
        inputTextAttributes.style[styleName] = input.style[styleName];
    }

    const container = document.createElement('div');
    container.className = 'instantSearchInputWrapper';
    input.parentNode.insertBefore(container, input);
    input.remove();

    const root = createRoot(container);
    root.render(<InstantSearchDropdown inputTextAttributes={inputTextAttributes} containerIndex={i} />);
}

const { useEffect, useState } = wp.element

export const useWeb3Auth = () => {
    const [web3AuthInstance, setWeb3AuthInstance] = useState()
    const web3authSdk = window.Web3auth
    const [user, setUser] = useState()
    const [account, setAccount] = useState()
    const [provider, setProvider] = useState()

    const init = async () => {
        const clientId = "BGN01ELH3OG56VmfXG8mcS776YSt2em_qrSCJmxxt9YZTWO2It8vkZEfUkfibpkYfV4Abi6VNJxGcymyAnxnerc"
        const web3AuthCtorParams = {
            clientId,
            chainConfig: { chainNamespace: "eip155", chainId: "0x1" },
        }

        const web3Auth = new web3authSdk.Web3Auth(web3AuthCtorParams)

        setWeb3AuthInstance(web3Auth)

        const chainConfig = {
            chainNamespace: "eip155",
            chainId: "0x1",
            rpcTarget: "https://mainnet.infura.io/v3/ab6162e91013410aa46123ef71b67da3",
            displayName: "Ethereum Mainnet",
            blockExplorer: "https://etherscan.io/",
            ticker: "ETH",
            tickerName: "Ethereum",
        }

        const metamaskAdapter = new window.MetamaskAdapter.MetamaskAdapter(chainConfig)
        web3Auth.configureAdapter(metamaskAdapter)

        const openLoginAdapter = new window.OpenloginAdapter.OpenloginAdapter({
            adapterSettings: {
                network: "mainnet",
                clientId,
                uxMode: "popup",
            },

            chainConfig,

            loginSettings: {
                relogin: true,
            },
        })
        web3Auth.configureAdapter(openLoginAdapter)

        const subscribeAuthEvents = (web3auth) => {
            web3auth.on("connected", async (data) => {
                console.log("Yeah!, you are successfully logged in", data)
                await setAddressField(web3Auth)
                const user = await web3Auth.getUserInfo()
                const provider = await web3Auth.connect()
                const web3 = new window.Web3(provider)
                const accounts = await web3.eth.getAccounts()
                setAccount(accounts[0])
                setUser(user)
            })

            web3auth.on("connecting", () => {
                console.log("connecting")
            })

            web3auth.on("disconnected", () => {
                console.log("disconnected")
                setWeb3AuthInstance(null)
                setAccount(null)
                setUser(null)
            })

            web3auth.on("errored", (error) => {
                console.log("some error or user have cancelled login request", error)
            })

            web3auth.on("MODAL_VISIBILITY", (isVisible) => {
                console.log("modal visibility", isVisible)
            })
        }

        subscribeAuthEvents(web3Auth)

        await web3Auth.initModal()
        return web3Auth
    }

    useEffect(() => {
        init()
    }, [])

    const login = async () => {
        if (!web3AuthInstance) {
            const web3Auth = await init()
            const provider = await web3Auth.connect()
            setProvider(provider)
        } else {
            const provider = await web3AuthInstance.connect()
            setProvider(provider)
        }
    }

    const logout = async () => {
        try {
            await web3AuthInstance.logout()
        } catch (error) {
            console.error(error.message)
        }
    }

    const setAddressField = async () => {
        for (const address of document.querySelectorAll("input[name^=recipient_blockchain_address_]")) {
            address.value = account
        }
    }

    return {
        login,
        logout,
        isLogged: !!web3AuthInstance,
        user,
        account,
    }
}
